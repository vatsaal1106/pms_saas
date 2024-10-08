<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\GlobalSetting;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class UpdateExchangeRates extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-exchange-rate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the exchange rates for all the currencies in currencies table.';


    /**
     * Execute the console command.
     *
     * @return mixed
     */

    public function handle()
    {
        $globalSetting = GlobalSetting::first();

        $currencyApiKey = ($globalSetting->currency_converter_key) ?: config('app.currency_converter_key');
        $currencyApiKeyVersion = $globalSetting->currency_key_version;

        $client = new Client();

        Company::with(['currencies', 'currency'])
            ->chunk(50, function ($companies) use ($currencyApiKey, $currencyApiKeyVersion, $client) {
                foreach ($companies as $company) {
                    $company->currencies->each(function ($currency) use ($currencyApiKey, $currencyApiKeyVersion, $company, $client) {
                        try {
                            $response = $client->request('GET', 'https://' . $currencyApiKeyVersion . '.currconv.com/api/v7/convert?q=' . $company->currency->currency_code . '_' . $currency->currency_code . '&compact=ultra&apiKey=' . $currencyApiKey);
                            $response = json_decode($response->getBody(), true);
                            $currency->exchange_rate = $response[$company->currency->currency_code . '_' . $currency->currency_code];
                            $currency->saveQuietly();
                        } catch (Exception $e) {
                            echo $e->getMessage();
                        }

                    });
                }
            });

        return Command::SUCCESS;

    }

}
