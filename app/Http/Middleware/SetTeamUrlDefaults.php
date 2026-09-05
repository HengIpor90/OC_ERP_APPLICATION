<?php

namespace App\Http\Middleware;

use App\Actions\Teams\CreateTeam;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetTeamUrlDefaults
{
    public function __construct(private CreateTeam $createTeam)
    {
    }

    /**
     * Set the default URL parameters for team-based routes.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $currentTeam = $user?->currentTeam ?? $user?->personalTeam();

        if ($user && ! $currentTeam) {
            $currentTeam = $this->createTeam->handle($user, $user->name."'s Team", isPersonal: true);
        }

        if ($user && $currentTeam && ! $user->isCurrentTeam($currentTeam)) {
            $user->switchTeam($currentTeam);
        }

        if ($currentTeam) {
            URL::defaults([
                'current_team' => $currentTeam->slug,
                'team' => $currentTeam->slug,
            ]);
        }

        return $next($request);
    }
}
