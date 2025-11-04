<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller as BaseController;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="School Management API",
 *     description="API Documentation for School Management System",
 *     @OA\Contact(
 *         email="admin@schoolmanagement.com"
 *     ),
 *     @OA\License(
 *         name="Apache 2.0",
 *         url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *     )
 * )
 * @OA\Server(
 *     url="/api/v1",
 *     description="API V1"
 * )
 * @OA\SecurityScheme(
 *     type="http",
 *     securityScheme="bearerAuth",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class Controller extends BaseController
{
}