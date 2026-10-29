<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class DeploymentOperationController extends Controller
{
    public function __invoke(Request $request, string $operation): JsonResponse
    {
        $expectedToken = config('services.deployment.token');
        $providedToken = $request->bearerToken();

        abort_unless(
            is_string($expectedToken)
                && strlen($expectedToken) >= 32
                && is_string($providedToken)
                && hash_equals($expectedToken, $providedToken),
            403,
        );

        [$command, $arguments] = match ($operation) {
            'migrate' => ['migrate', ['--force' => true]],
            'cache-clear' => ['optimize:clear', []],
            default => abort(404),
        };

        $exitCode = Artisan::call($command, $arguments);

        if ($exitCode !== 0) {
            Log::error('A remote deployment operation failed.', [
                'operation' => $operation,
                'output' => Artisan::output(),
            ]);

            return response()->json([
                'message' => 'The requested deployment operation failed. Check the application log.',
            ], 500);
        }

        return response()->json([
            'message' => 'The requested deployment operation completed.',
            'operation' => $operation,
        ]);
    }
}
