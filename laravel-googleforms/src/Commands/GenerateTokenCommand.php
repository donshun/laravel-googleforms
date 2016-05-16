<?php

    /**
     * Created by Elmar <e.abdurayimov@gmail.com> Abdurayimov
     *
     * @copyright (C)Copyright 2016 elmar.eatech.org
     *               Date: 5/13/16
     *               Time: 4:43 PM
     */
    namespace Akaramires\GoogleForms\Commands;

    use Google_Client;
    use Illuminate\Console\Command;

    class GenerateTokenCommand extends Command
    {
        protected $signature = 'google-forms:token';

        protected $description = '';

        private static $app_name = 'Laravel Google Forms';
        private static $scopes   = 'https://www.googleapis.com/auth/drive https://www.googleapis.com/auth/forms https://www.googleapis.com/auth/script.external_request';

        public function handle()
        {
            $clientSecretFile = storage_path(config('googleforms.config_file'));
            $tokenFile = storage_path(config('googleforms.token_file'));

            $client = new Google_Client();
            $client->setApplicationName(self::$app_name);
            $client->setScopes(self::$scopes);
            $client->setAuthConfigFile($clientSecretFile);
            $client->setAccessType('offline');

            if (file_exists($tokenFile)) {
                $accessToken = \File::get($tokenFile);
            } else {
                // Request authorization from the user.
                $authUrl = $client->createAuthUrl();

                $this->info("Open the following link in your browser:\n" . $authUrl);
                $authCode = trim($this->ask('Enter verification code:'));

                // Exchange authorization code for an access token.
                $accessToken = $client->authenticate($authCode);

                // Store the credentials to disk.
                \File::put($tokenFile, $accessToken);
                $this->info("Credentials saved to " . $tokenFile);
            }

            $client->setAccessToken($accessToken);

            // Refresh the token if it's expired.
            if ($client->isAccessTokenExpired()) {
                $client->refreshToken($client->getRefreshToken());
                \File::put($tokenFile, $client->getAccessToken());
            }
        }
    }