<?php

namespace IO\Tests\Feature;

use IO\Services\ItemSearch\SearchPresets\BasketItems;
use IO\Services\ItemSearch\Services\ItemSearchService;
use Mockery;
use IO\Tests\TestCase;
use IO\Services\BasketService;
use Plenty\Legacy\Repositories\ItemDataLayerRepository;
use Plenty\Modules\Item\DataLayer\Contracts\ItemDataLayerRepositoryContract;
use Plenty\Modules\Item\Variation\Models\Variation;
use Plenty\Modules\Item\VariationStock\Models\VariationStock;
use Plenty\Modules\Basket\Models\Basket;
use Plenty\Modules\Basket\Repositories\BasketRepository;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;

use Illuminate\Support\Facades\Session;

/**
 * User: mklaes
 * Date: 08.08.18
 */
class BasketServiceItemRepoTest extends TestCase
{
    /** @var BasketService $basketService  */
    protected $basketService;
    protected $variation;
    protected $variationStock;

    protected $itemDataLayerRepoMock;
    /** @var ItemSearchService $itemSearchServiceMock  */
    protected $itemSearchServiceMock;

    protected $basketRepoMock;

    protected function setUp()
    {
        parent::setUp();

        // $this->itemDataLayerRepoMock = Mockery::mock(ItemDataLayerRepositoryContract::class);
        // $this->app->instance(ItemDataLayerRepositoryContract::class , $this->itemDataLayerRepoMock);

        $this->itemSearchServiceMock = Mockery::mock(ItemSearchService::class);
        app()->instance(ItemSearchService::class, $this->itemSearchServiceMock);

        $this->basketService = pluginApp(BasketService::class);
        $this->variation = factory(Variation::class)->create([
            'minimumOrderQuantity' => 1.00
        ]);
        $this->variationStock = factory(VariationStock::class)->make([
           'varationId' => $this->variation->id,
           'warehouseId' => $this->variation->mainWarehouseId,
            'netStock' => 1000
        ]);

        // $this->basketRepoMock = Mockery::mock(BasketRepository::class['load']);
        // app()->instance(BasketRepositoryContract::class, $this->basketRepoMock);

        // set referrer id in session
    }

    /** @test */
    public function it_adds_an_item_to_the_basket()
    {
        $variation = $this->variation;
        $item1 = ['variationId' => $variation['id'], 'quantity' => 1, 'template' => '', 'basketItemOrderParams' => [] ];
//        $item1 = factory(Variation::class)->make();
        $basket = factory(Basket::class)->make();


        $esMockData = $this->getTestJsonData();
        $esMockData['documents'][0]['id'] = $variation['id'];

        $this->itemSearchServiceMock
            ->shouldReceive('getResults')
            ->with(Mockery::any())//BasketItems::getSearchFactory(['variationIds' => [$variationId],'quantities' => [$variationId => 1]])
            ->andReturn($esMockData);

        Session::shouldReceive('getId')
            ->once()
            ->andReturn($basket->sessionId);

        // $this->basketRepoMock->shouldReceive('load')
        //     ->once()
        //     ->andReturn($basket);

        $result = $this->basketService->addBasketItem($item1);

        $this->assertEquals($variation->id, $result[0]['variationId']);
        $this->assertEquals(1, $result[0]['quantity']);
        $this->assertCount(1, $result);
    }

    /** @test */
    public function it_updates_an_item_in_the_basket()
    {
        $variation = $this->variations[0];
        $item1 = ['variationId' => $variation['id'], 'quantity' => 1, 'template' => '', 'referrerId' => 1];

        $this->basketService->addBasketItem($item1);
        $result = $this->basketService->addBasketItem($item1);

        $this->assertEquals($variation->id, $result[0]['variationId']);
        $this->assertEquals(2, $result[0]['quantity']);
        $this->assertCount(1, $result);
    }

    /** @test */
    public function it_removes_an_item_from_the_basket()
    {
        $variation = $this->variations[0];
        $item1 = ['variationId' => $variation['id'], 'quantity' => 1, 'template' => '', 'referrerId' => 1];

        $basketItems = $this->basketService->addBasketItem($item1);
        $result = $this->basketService->deleteBasketItem($basketItems['data'][0]['id']);

        $this->assertEmpty($result);
    }

    /**
     * helper method to get the item search result json
     * @return mixed
     */
    public function getTestJsonData()
    {
        $file = __DIR__ . "/../Fixtures/complete_basket_response.json";
        return json_decode(
            file_get_contents($file),
            true
        );
    }
}