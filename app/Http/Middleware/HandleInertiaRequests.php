<?php

namespace App\Http\Middleware;

use App\Models\Parish;
use App\Models\Rol;
use App\Models\Sex;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {   
        $user = null;
        if(
            $request->user() &&
            $request->user()?->sex_id &&
            $request->user()?->rol_id
        ){
            $sex = Sex::query()->where('id',$request->user()->sex_id)->first();

            $role = Rol::query()->where('id',$request->user()->rol_id)->first();
            
            // $parish = Parish::query()->where('id',$request->user()->parish_id)->first();
            $user = [
                "id" => $request->user()->id,
                "name" => $request->user()->name,
                "email" => $request->user()->email,
                "sex" => $sex->abbreviation,
                "role" => $role->name,
                //"parish" => $parish->name
            ];
        }
        
        return [
            ...parent::share($request),
            'name' => "Gestión de encuestas.",//config('app.name'),
            'auth' => [
                //'user' => $request->user(),
                'user' => $user
            ],
        ];
    }
}
