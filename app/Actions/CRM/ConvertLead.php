<?php

namespace App\Actions\CRM;

use App\Enums\DealStatus;
use App\Enums\LeadStatus;
use App\Models\CRM\Company;
use App\Models\CRM\Contact;
use App\Models\CRM\Deal;
use App\Models\CRM\Lead;
use App\Models\CRM\Pipeline;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ConvertLead
{
    /**
     * @param  array<string, mixed>  $overrides
     */
    public function execute(User $user, Lead $lead, array $overrides = []): Lead
    {
        return DB::transaction(function () use ($user, $lead, $overrides) {
            $company = null;

            if (! empty($lead->company_name)) {
                $company = Company::firstOrCreate(
                    ['account_id' => $lead->account_id, 'name' => $lead->company_name],
                    ['owner_id' => $lead->owner_id ?? $user->id],
                );
            }

            [$firstName, $lastName] = $this->splitName($lead->contact_name);

            $contact = Contact::create([
                'account_id' => $lead->account_id,
                'company_id' => $company?->id,
                'owner_id' => $lead->owner_id ?? $user->id,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $lead->email,
                'phone' => $lead->phone,
            ]);

            $pipeline = Pipeline::where('account_id', $lead->account_id)
                ->orderByDesc('is_default')
                ->orderBy('position')
                ->firstOrFail();

            $firstStage = $pipeline->stages()->orderBy('position')->firstOrFail();

            $deal = Deal::create([
                'account_id' => $lead->account_id,
                'pipeline_id' => $pipeline->id,
                'stage_id' => $firstStage->id,
                'company_id' => $company?->id,
                'contact_id' => $contact->id,
                'owner_id' => $lead->owner_id ?? $user->id,
                'title' => $overrides['title'] ?? ($lead->company_name ?? $lead->contact_name),
                'amount' => $overrides['amount'] ?? 0,
                'currency' => $overrides['currency'] ?? 'EUR',
                'probability' => $firstStage->probability,
                'expected_close_date' => $overrides['expected_close_date'] ?? null,
                'status' => DealStatus::Open,
            ]);

            $lead->forceFill([
                'status' => LeadStatus::Converted,
                'converted_at' => now(),
                'converted_company_id' => $company?->id,
                'converted_contact_id' => $contact->id,
                'converted_deal_id' => $deal->id,
            ])->save();

            return $lead->refresh();
        });
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name), 2) ?: [];

        return [$parts[0] ?? $name, $parts[1] ?? ''];
    }
}
