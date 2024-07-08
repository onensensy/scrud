<?php

namespace Sensy\Scrud\app\Controller;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Lab404\Impersonate\Services\ImpersonateManager;

class ImpersonateController extends Controller
{
    protected $impersonateManager;

    public function __construct(ImpersonateManager $impersonateManager)
    {
        $this->impersonateManager = $impersonateManager;
    }

    public function impersonate(Request $req, User $user)
    {
        $original = auth()->user();

        if ($original->canImpersonate() && $user->canBeImpersonated()) {

            $this->impersonateManager->take($original, $user);
            $this->_impersonateAction($req, $original, 'user.impersonating', $user);
            return redirect()->back();
        }

        return redirect()->back()->withErrors('You do not have permission to impersonate this user.');
    }

    public function leaveImpersonate(Request $req)
    {
        if ($this->impersonateManager->isImpersonating()) {
            $original = User::find($this->impersonateManager->getImpersonatorId());
            $impersonated = auth()->user();

            $this->impersonateManager->leave();
            $this->_impersonateAction($req, $original, "exited.impersonate", $impersonated);

            return redirect()->back();
        }

        return redirect()->back()->withErrors('You are not impersonating any user.');
    }

    protected function _impersonateAction(Request $req, User $original, string $action, User $user)
    {
        # Impersonate audits
//        return ImpersonateAudit::create([
//            "ip_address" => $req->ip(),
//            "impersonator_id" => $original->id,
//            "impersonator_name" => $original->username,
//            "impersonated_id" => $user->id,
//            "impersonated_name" => $user->username,
//            "action" => $action,
//            "status" => $this->impersonateManager->isImpersonating()
//        ]);

//        return true;
    }
}
