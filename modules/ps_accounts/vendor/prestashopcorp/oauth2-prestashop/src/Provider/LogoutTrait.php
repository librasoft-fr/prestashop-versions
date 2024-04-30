<?php

namespace PrestaShop\OAuth2\Client\Provider;

trait LogoutTrait
{
    /**
     * @var string
     */
    protected $postLogoutCallbackUri;

    /**
     * @return string
     */
    public function getBaseSessionLogoutUrl(): string
    {
        return 'https://oauth.prestashop.com/oauth2/sessions/logout';
    }

    /**
     * Builds the session logout URL.
     *
     * @param array $options
     *
     * @return string Logout URL
     *
     * @throws \Exception
     */
    public function getLogoutUrl(array $options = []): string
    {
        $base = $this->getBaseSessionLogoutUrl();
        $params = $this->getLogoutParameters($options);
        $query = $this->getLogoutQuery($params);

        return $this->appendQuery($base, $query);
    }

    /**
     * @param array $options
     *
     * @return string[]
     *
     * @throws \Exception
     */
    protected function getLogoutParameters(array $options): array
    {
        if (empty($options['id_token_hint'])) {
            // $options['id_token_hint'] = $this->getSessionAccessToken()->getValues()['id_token'];
            throw new \Exception('Missing id_token_hint required parameter');
        }

        if (empty($options['post_logout_redirect_uri'])) {
            if (!empty($this->postLogoutCallbackUri)) {
                $options['post_logout_redirect_uri'] = $this->postLogoutCallbackUri;
            } else {
                throw new \Exception('Missing post_logout_redirect_uri required parameter');
            }
        }

        return $options;
    }

    /**
     * Builds the logout URL's query string.
     *
     * @param array $params Query parameters
     *
     * @return string Query string
     */
    protected function getLogoutQuery(array $params): string
    {
        return $this->buildQueryString($params);
    }
}
