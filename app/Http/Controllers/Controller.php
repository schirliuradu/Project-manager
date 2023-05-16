<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Project Manager OpenApi Documentation",
 *      description="Project Manager OpenApi Documentation",
 *      @OA\Contact(
 *          email="schirliuradu@gmail.com"
 *      ),
 *      @OA\License(
 *          name="Apache 2.0",
 *          url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *      )
 * )
 *
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="Project Manager API Server"
 * )
 *
 * @OA\Tag(
 *     name="Projects",
 *     description="API Endpoints of Projects"
 * ),
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 *
 * @OA\Parameter(
 *     parameter="pageParameter",
 *     name="page",
 *     description="Page",
 *     required=true,
 *     in="query",
 *     @OA\Schema(
 *         type="integer"
 *     )
 * ),
 *
 * @OA\Parameter(
 *     parameter="perPageParameter",
 *     name="perPage",
 *     description="Per page items",
 *     required=true,
 *     in="query",
 *     @OA\Schema(
 *         type="integer"
 *     )
 * ),
 *
 * @OA\Parameter(
 *     parameter="sortByParameter",
 *     name="sortBy",
 *     description="Results sorting options.",
 *     required=true,
 *     in="query",
 *     @OA\Schema(
 *         type="string",
 *         enum={"alpha_desc", "alpha_asc", "create", "update"}
 *     )
 * ),
 *
 * @OA\Parameter(
 *     parameter="withClosedParameter",
 *     name="withClosed",
 *     description="Parameter through which require both open and closed projects.",
 *     required=false,
 *     in="query",
 *     @OA\Schema(
 *         type="integer",
 *         enum={0, 1}
 *     )
 * ),
 *
 * @OA\Parameter(
 *     parameter="onlyClosedParameter",
 *     name="onlyClosed",
 *     description="Parameter through which require only closed projects.",
 *     required=false,
 *     in="query",
 *     @OA\Schema(
 *         type="integer",
 *         enum={0, 1}
 *     )
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
