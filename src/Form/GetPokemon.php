<?php

namespace Drupal\pokedex\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\pokedex\Service\pokedexService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form to get a Pokemon
 */
class GetPokemon extends FormBase {
    
    /**
     * Service to make a request to Pokedex.
     * 
     * @var Drupal\pokedex\Service\pokedexService
     */
    protected $pokedexService;

    public function __construct(pokedexService $pokedexService) {
        $this->pokedexService = $pokedexService;
    }

    /**
     * Inject service.
     */
    public static function create(ContainerInterface $container){
        return new static(
            $container->get('pokedex.pokedexService'),
        );
    }

  /**
   * Returns a unique string identifying the form.
   *
   * The returned ID should be a unique string that can be a valid PHP function
   * name, since it's used in hook implementation names such as
   * hook_form_FORM_ID_alter().
   *
   * @return string
   *   The unique string identifying the form.
   */
    public function getFormId() {
        return 'get_pokemon_form';
    }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
    public function buildForm(array $form, FormStateInterface $form_state) {
        $form = [
            '#title' => $this->t('Get your Pokemon'),
            '#attributes' => [
                'id' => 'get_your_pokemon',
            ]
        ];
        $form['birth_date'] = [
            '#type' => 'date',
            '#title' => $this->t('Set your born date'),
            '#ajax' => [
                'event' => 'change',
                'callback' => '::getYourPokemon',
                'wrapper' => 'pokemon_result'
            ],
        ];
        $form['pokemon_result'] = [
            '#prefix' => '<div id="pokemon_result">',
            '#sufix' => '</div>'
        ];

        $form['#attached']['library'][] = 'pokedex/pokemon_card';
        return $form;
    }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state){}

  /**
   * Ajax to get a Pokemon.
   */
  public function getYourPokemon(array &$form, FormStateInterface &$form_state){
    $trigger = $form_state->getTriggeringElement()['#value'];

    $date = explode('-',$trigger);
    $total = array_sum($date);
    
    $get = '/api/v2/pokemon';
    do {
        $total = (int) ($total / $date[1]);
        $pokemon = $this->pokedexService->request($get . '/' . $total);
    } while (is_null($pokemon));
    $pokemon_data = [
        'name' => $pokemon['name'],
        'imageGame' => $pokemon['sprites']['front_default'],
        'image' => $pokemon['sprites']['other']['dream_world']['front_default'],
        'experience' => $pokemon['base_experience'],
        'hp' => $pokemon['stats'][0]['base_stat'],
        'attack' => $pokemon['stats'][1]['base_stat'],
        'defense' => $pokemon['stats'][2]['base_stat'],
        'special' => $pokemon['stats'][3]['base_stat'],
    ];
    $form['pokemon_result']['#prefix'] = '<div id="pokemon_result">' . $this->pokemonTemplate($pokemon_data);
    return $form['pokemon_result'];
  }

  /**
   * @param array $pokemon 
   *    Pokemon information.
   */
  public function pokemonTemplate($pokemon){
    return '
    <template id="card">
        <article class="card">
            <img
            src="' . $pokemon['image'] . '"
            alt="'. $this->t('Image of ') . $pokemon['name'] . '"
            class="card-body-img"
            />
            <div class="card-data">
                <div class="card-data-body">
                    <h1 class="card-data-body-title">
                    ' . $pokemon['name'] . '
                    <span>' . $pokemon['hp'] . 'hp</span>
                    </h1>
                    <p class="card-body-text">' . $pokemon['experience'] . ' exp</p>
                </div>
                <div class="card-data-footer">
                    <div class="card-data-footer-social">
                    <h3>' . $pokemon['attack'] . 'k</h3>
                    <p>' . $this->t('Attack') . '</p>
                    </div>
                    <div class="card-data-footer-social">
                    <h3>' . $pokemon['special'] . 'k</h3>
                    <p>' . $this->t('Special attack') . '</p>
                    </div>
                    <div class="card-data-footer-social">
                    <h3>' . $pokemon['defense'] . 'k</h3>
                    <p>' . $this->t('Defense') . '</p>
                    </div>
                </div>
            </div>
        </article>
    </template>
    ';
    }
}
