<?php

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Str;

class Form implements \JsonSerializable
{
    /** @var integer */
    protected $productId;

    /** @var integer */
    protected $formId;

    /** @var string */
    protected $link;

    /** @var array */
    protected $fields;

    /** @var string|null */
    protected $password;

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string|null $password
     *
     * @return Form
     */
    public function setPassword(?string $password): Form
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getLink(): string
    {
        return $this->link;
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        return [
            'product_id' => $this->productId,
            '_wp_http_referer' => $this->link,
            'input_1.3' => 'Vezetéknév',
            'input_1.6' => 'Keresztnév',
            'input_2' => 'Neptun kód',
            'input_2_2' => 'Igényelt IP',
            'gform_old_submit' => $this->formId,
            'add-to-cart' => $this->productId,
            'gform_form_id' => $this->formId,
            'redirect_to' => 'http://bmejegy.hu/penztar/'
        ];
    }

    /**
     * Validate submitted data, server sidely
     */
    public function validateExternally()
    {
        $client = new Client([
            RequestOptions::COOKIES => true,
            RequestOptions::ALLOW_REDIRECTS => true
        ]);

        $hasher = new Hasher();

        $cookieJar = CookieJar::fromArray([
            'wp-postpass_' . md5('https://www.bmejegy.hu') => $hasher->hash($this->getPassword())
        ], 'www.bmejegy.hu');

        $result = $client->post($this->getLink(), [
            'form_params' => $this->getFields(),
            'cookies' => $cookieJar
        ]);

        if ($result->getStatusCode() !== 200) {
            throw new \RuntimeException('Cart adding failed with http code: ' . $result->getStatusCode());
        }

        if (Str::contains($body = $result->getBody()->getContents(), 'hozzá lett adva a kosaradhoz')) {
            return;
        }

        if (Str::contains($body, 'wp-login.php?action=postpass')) {
            throw new \RuntimeException('Invalid password');
        }

        preg_match_all('/\<li id=\'field[^\>]*\>\<label [^\>]*\>[^\<]*.+?\<\/li\>/m', $body, $matches);

        foreach ($matches[0] as $field) {
            preg_match_all('/\<label[^\>]*\>([^\<]*).*validation_message\'\>([^\<]*).*/m', $field, $error);
            if (!empty($error[1])) {
                throw new \RuntimeException('Validation for field (' . $error[1][0] . ') with message: ' . $error[2][0]);
            }
        }

        throw new \RuntimeException('Cart adding failed with http code: ' . $result->getStatusCode());
    }

    public function jsonSerialize()
    {
        $this->validateExternally();

        return [
            'fields' => $this->getFields(),
            'productLink' => $this->link,
            'password' => $this->password
        ];
    }
}
