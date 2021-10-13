# DeFactuur class

> De Factuur is an online invoicing web application

## About

PHP DeFactuur is a (wrapper)class to communicate with [De Factuur](https://www.defactuur.be).

## License

PHP DeFactuur is [BSD](http://classes.verkoyen.eu/overview/bsd) licensed.

## Initialisation

Using symfony/httpclient:

    use Nyholm\Psr7\Factory\Psr17Factory;
    use Symfony\Component\HttpClient\Psr18Client;

    $deFactuur = new DeFactuur(
        new Psr18Client(),
        new Psr17Factory(),
        new Psr17Factory(),
        'your_api_token'
    );

Using Guzzle:

    $deFactuur = new \SumoCoders\DeFactuur\DeFactuur(
        new \GuzzleHttp\Client(),
        new \Nyholm\Psr7\Factory\Psr17Factory(),
        new \Nyholm\Psr7\Factory\Psr17Factory(),    
        'your_api_token'
    );

You can replace Psr17Factory with your own implementations of PSR-17's RequestFactoryInterface and StreamFactoryInterface.

## Using DeFactuur as a Service

Add the following to your services.yml:

    SumoCoders\DeFactuur\DeFactuur:
        arguments:
            $apiToken: '%your.api.token%'

If you use auto-wiring, that's it!

## Documentation

Each method in the class is well documented using PHPDoc.
