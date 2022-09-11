<?php

namespace Drupal\pokedex\Service;

use Drupal\Core\Http\ClientFactory;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Component\Serialization\Json;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ConnectException;

/**
 * Create a service for consuming the openlibrary.org API
 */
class pokedexService
{

  /**
   * Factory class for Client Class.
   *
   * @var Drupal\Core\Http\ClientFactory
   */
  protected $clientFactory;

  /**
   * Cache service.
   *
   * @var Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * Main path.
   * 
   * @var string
   */

  /**
   * Constructor
   */
  public function __construct(
    ClientFactory $clientFactory,
    CacheBackendInterface $cache
  ) {
    $this->clientFactory = $clientFactory;
    $this->cache = $cache;
  }

  /**
   * Make a request to the API
   *
   * @return Array List of books. Null if there was an exception
   */
  public function request($get = NULL)
  {
    try {
      //Returns a GuzzleHttp/Client object | ConnectException
      $client = $this->clientFactory->fromOptions(['base_uri' => 'https://pokeapi.co']);
      //Returns a GuzzleHttp/Psr7/Response object | GuzzleException
      $response = $client->get($get, []);
    } catch (RequestException $e) {
      return NULL;
    } catch (GuzzleException $e) {
      return NULL;
    } catch (ConnectException $e) {
      return NULL;
    }
    //Select the GuzzleHttp/Psr7/Stream object from the Response object
    $body = $response->getBody();
    //Convert the Stream object into an array
    $data = Json::decode($body);
    return $data;
  }
}
