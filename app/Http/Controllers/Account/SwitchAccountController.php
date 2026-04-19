<?php

namespace App\Http\Controllers\Account;

use App\Actions\Account\SwitchAccountAction;
use App\Models\Account;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SwitchAccountController
{
    public function __invoke(Request $request, SwitchAccountAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'account_id' => ['required', 'integer', 'exists:accounts,id'],
        ]);

        $account = Account::findOrFail($validated['account_id']);

        $action->execute(Auth::user(), $account);

        return redirect()->route('crm.dashboard');
    }
}
