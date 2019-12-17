<?php
/**
 * Quote Ninja (https://invoiceninja.com)
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2019. Quote Ninja LLC (https://invoiceninja.com)
 *
 * @license https://opensource.org/licenses/AAL
 */

namespace App\Jobs\Quote;

use App\Factory\QuoteInvitationFactory;
use App\Models\Quote;
use App\Models\QuoteInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Debug\Exception\FatalThrowableError;

class CreateQuoteInvitations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $quote;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Quote $quote)
    {

        $this->quote = $quote;

    }

    public function handle()
    {
        
        $contacts = $this->quote->client->contacts;

        $contacts->each(function ($contact) {

            $invitation = QuoteInvitation::whereCompanyId($this->quote->company_id)
                                        ->whereClientContactId($contact->id)
                                        ->whereQuoteId($this->quote->id)
                                        ->first();

            if(!$invitation && $contact->send_invoice) {
                $ii = QuoteInvitationFactory::create($this->quote->company_id, $this->quote->user_id);
                $ii->quote_id = $this->quote->id;
                $ii->client_contact_id = $contact->id;
                $ii->save();
            }
            else if($invitation && !$contact->send_invoice) {
                $invitation->delete();
            }

        });

    }
}