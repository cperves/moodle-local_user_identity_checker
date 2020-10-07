<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * local_user_identity_checker library
 *
 * @package    local_user_identity_checker
 * @author Daniel Bessey <dbessey@unistra.fr>
 * @author Matthieu Fuchs <matfuchs@unistra.fr>
 * @author Céline Pervès <cperves@unistra.fr>
 * @copyright Université de Strasbourg 2020 {@link http://unistra.fr}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace unistra\user_identity_checker\jwt;
defined('MOODLE_INTERNAL') || die();

use BadMethodCallException;
use DateTime;
use DateTimeZone;
use InvalidArgumentException;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token;
use OutOfBoundsException;

class InvalidTokenException extends InvalidArgumentException {
}

function get_token($rawtoken): Token {
    try {
        $token = (new Parser())->parse($rawtoken);
    } catch (InvalidArgumentException $e) {
        throw new InvalidTokenException('invalid_token');
    }
    return $token;
}

function get_token_data(Token $token): array {
    // Retrieve jwt from request headers if request type is GET.
    try {
        $claims = $token->getClaim("eole31");
    } catch (OutOfBoundsException $e) {
        throw new InvalidTokenException('invalid_claim');
    }
    // Use dashboard url from token to retrieve public key.
    // Validate token , if invalid : display error, else, continue.
    return [
        'jti' => $claims->dashboard->jti,
        'validation_url' => $claims->dashboard->validation_url,
        'redirect_url' => $claims->dashboard->redirect_url,
        'dashboard_url' => $claims->dashboard->iss,
    ];
}

function get_dashboard_public_key($url) {
    global $DB;
    $url = rtrim($url, "/");
    $r = $DB->get_record('user_identity_checker_jwt', ['dashboardurl' => $url]);
    if (!$r) {
        throw new InvalidTokenException('unknow_dashboard');
    }
    return $r->publickey;
}

function validate_token(Token $token, string $publickey) {
    $signer = new Sha256();
    $key = new Key($publickey);
    try {
        $isvalid = $token->verify($signer, $key);
    } catch (BadMethodCallException $e) {
        throw new InvalidTokenException('token_not_signed');
    }
    if (!$isvalid) {
        throw new InvalidTokenException('invalid_token_signature');
    }
}

function make_registration_token($username, $jti, $issuer, $privatekey) {
    $now = new DateTime("now", new DateTimeZone("UTC"));
    $token = (new Builder())
        ->withClaim(
            'eole31',
            [
                "moodle" => [
                    "sub" => $username,
                    "jti" => $jti,
                    "iss" => $issuer,
                    "exp" => $now->getTimestamp() + 600,
                ],
            ]
        )
        ->getToken(
            new Sha256(),
            new Key($privatekey)
        );
    return $token;
}

function notify_dashboard($token, $url) {
    $data = [
        'token' => (string)$token,
    ];
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($data),
        CURLOPT_TIMEOUT => 3,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_HEADER => 1,
    ]);
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($status == 201) {
        return true;
    } else {
        return false;
    }
}

function register($username, $jti, $privatekey, $dashboardnotificationurl, $issuer) {
    $token = make_registration_token($username, $jti, $issuer, $privatekey);
    return notify_dashboard($token, $dashboardnotificationurl);
}
