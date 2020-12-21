<?php
/**
 * NOTE: This class is auto generated by the swagger code generator program.
 * https://github.com/swagger-api/swagger-codegen
 * Do not edit the class manually.
 */

namespace SquareConnect\Api;

use \SquareConnect\Configuration;
use \SquareConnect\ApiClient;
use \SquareConnect\ApiException;
use \SquareConnect\ObjectSerializer;

/**
 * InventoryApiTest Class Doc Comment
 *
 * @category Class
 * @package  SquareConnect
 * @author   Square Inc.
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache Licene v2
 * @link     https://squareup.com/developers
 */
class InventoryApiTest extends \PHPUnit_Framework_TestCase
{

    private static $api_instance;
    private static $catalog_api_instance;
    private static $test_accounts;
    private static $location_id;
    private static $catalog_object_id;
    private $tea = [
      "type" => "ITEM",
      "id" => "#Tea",
      "item_data" => [
        "name" => "Tea",
        "description" => "Hot leaf juice",
        "abbreviation" => "Te",
        "variations" => [
          [
            "type" => "ITEM_VARIATION",
            "id" => "#self::SMALL_TEA_CLIENT_ID",
            "item_variation_data" => [
              "name" => "Small",
              "item_id" => "#Tea",
              "pricing_type" => "FIXED_PRICING",
              "price_money" => [
                "amount" => 150.0,
                "currency" => "USD"
              ]
            ]
          ]
        ]
      ]
    ];

    /**
     * Setup before running each test case
     */
    public static function setUpBeforeClass() {
      self::$api_instance = new \SquareConnect\Api\InventoryApi();
      self::$catalog_api_instance = new \SquareConnect\Api\CatalogApi();
      self::$test_accounts = new \SquareConnect\TestAccounts();
      // Configure OAuth2 access token for authorization: oauth2
      $account = self::$test_accounts->{'US-Prod'};
      $access_token = $account->{'access_token'};
      self::$location_id = $account->{'location_id'};
      Configuration::getDefaultConfiguration()->setAccessToken($access_token);
    }

    protected function setUp() {
      $response = $this->searchItemVariation();
      if (count($response->getObjects()) == 0) {
          $this->createTestItemVariation();
          $response = $this->searchItemVariation();
      }

      self::$catalog_object_id = $response->getObjects()[0]->getId();
    }

    protected function createTestItemVariation() {
      $body = new \SquareConnect\Model\BatchUpsertCatalogObjectsRequest([
        "idempotency_key" => uniqid(),
        "batches" => [
          [
            "objects" => [$this->tea]
          ]
        ]
      ]);

      self::$catalog_api_instance->batchUpsertCatalogObjects($body);
    }

    protected function searchItemVariation() {
      $query = new \SquareConnect\Model\SearchCatalogObjectsRequest([
        "object_types" => [
          "ITEM_VARIATION"
        ],
        "limit" => 1,
        "include_deleted_objects" => false,
        "include_related_objects" => false,
      ]);

      return self::$catalog_api_instance->searchCatalogObjects($query);
    }

    /**
     * Clean up after running each test case
     */
    public static function tearDownAfterClass() {

    }

    /**
     * Test case for batchChangeInventory
     *
     * BatchChangeInventory
     *
     */
    public function test_batchChangeInventory() {
      $body = new \SquareConnect\Model\BatchChangeInventoryRequest([
          "idempotency_key" => uniqid(),
          "changes" => [
            [
              "type" => "PHYSICAL_COUNT",
              "physical_count" => [
                "catalog_object_id" => self::$catalog_object_id,
                "state" => "IN_STOCK",
                "location_id" => self::$location_id,
                "quantity" => "1",
                "occurred_at" => date("Y-m-d\TH:i:s\Z", time())
              ]
            ]
          ]
      ]);
      $response = self::$api_instance->batchChangeInventory($body);
      $this->assertInstanceOf(
          '\SquareConnect\Model\BatchChangeInventoryResponse',
          $response
      );
      $this->assertEmpty($response->getErrors());
    }
    /**
     * Test case for batchRetrieveInventoryChanges
     *
     * BatchRetrieveInventoryChanges
     *
     */
    public function test_batchRetrieveInventoryChanges() {
      $this->physicalCountInventoryChange(self::$catalog_object_id, self::$location_id);
      $body = new \SquareConnect\Model\BatchRetrieveInventoryChangesRequest([
          "catalog_object_ids" => [ self::$catalog_object_id ]
      ]);
      $response = self::$api_instance->batchRetrieveInventoryChanges($body);
      $this->assertInstanceOf(
          '\SquareConnect\Model\BatchRetrieveInventoryChangesResponse',
          $response
      );
      $this->assertEmpty($response->getErrors());
      $this->assertGreaterThanOrEqual(1, count($response->getChanges()));
      $this->assertEquals(self::$catalog_object_id, $response->getChanges()[0]->getPhysicalCount()->getCatalogObjectId());
    }
    /**
     * Test case for batchRetrieveInventoryCounts
     *
     * BatchRetrieveInventoryCounts
     *
     */
    public function test_batchRetrieveInventoryCounts() {
      $this->physicalCountInventoryChange(self::$catalog_object_id, self::$location_id);
      $body = new \SquareConnect\Model\BatchRetrieveInventoryCountsRequest([
          "catalog_object_ids" => [ self::$catalog_object_id ]
      ]);
      $counts = self::$api_instance->batchRetrieveInventoryCounts($body);
      $this->assertInstanceOf(
          '\SquareConnect\Model\BatchRetrieveInventoryCountsResponse',
          $counts
      );
      $this->assertEmpty($counts->getErrors());
      $this->assertGreaterThanOrEqual(1, count($counts->getCounts()));
      $this->assertEquals(self::$catalog_object_id, $counts->getCounts()[0]->getCatalogObjectId());
    }
    /**
     * Test case for retrieveInventoryAdjustment
     *
     * RetrieveInventoryAdjustment
     *
     */
    public function test_retrieveInventoryAdjustment() {
      $batch_change_inventory_body = new \SquareConnect\Model\BatchChangeInventoryRequest([
          "idempotency_key" => uniqid(),
          "changes" => [
            [
              "type" => "ADJUSTMENT",
              "adjustment" => [
                "catalog_object_id" => self::$catalog_object_id,
                "from_state" => "NONE",
                "to_state" => "IN_STOCK",
                "location_id" => self::$location_id,
                "quantity" => "1",
                "occurred_at" => date("Y-m-d\TH:i:s\Z", time())
              ]
            ]
          ]
      ]);
      self::$api_instance->batchChangeInventory($batch_change_inventory_body);

      $batch_retrieve_change_body = new \SquareConnect\Model\BatchRetrieveInventoryChangesRequest([
          "catalog_object_ids" => [ self::$catalog_object_id ],
          "types" => [ "ADJUSTMENT" ]
      ]);
      $retrieve_response = self::$api_instance->batchRetrieveInventoryChanges($batch_retrieve_change_body);

      $adjustment = $retrieve_response->getChanges()[0]->getAdjustment();
      $response = self::$api_instance->retrieveInventoryAdjustment($adjustment->getId());
      $this->assertInstanceOf(
          '\SquareConnect\Model\RetrieveInventoryAdjustmentResponse',
          $response
      );
      $this->assertEquals($response->getAdjustment(), $adjustment);
    }
    /**
     * Test case for retrieveInventoryChanges
     *
     * RetrieveInventoryChanges
     *
     */
    public function test_retrieveInventoryChanges() {
      $this->physicalCountInventoryChange(self::$catalog_object_id, self::$location_id);

      $changes = self::$api_instance->retrieveInventoryChanges(self::$catalog_object_id);
      $this->assertInstanceOf(
          '\SquareConnect\Model\RetrieveInventoryChangesResponse',
          $changes
      );
      $this->assertGreaterThanOrEqual(1, count($changes->getChanges()));

      $change = $changes->getChanges()[0];
      if ($change->getType() == 'PHYSICAL_COUNT') {
        $this->assertEquals(self::$catalog_object_id, $change->getPhysicalCount()->getCatalogObjectId());
      } else {
        $this->assertEquals(self::$catalog_object_id, $change->getAdjustment()->getCatalogObjectId());
      };
    }
    /**
     * Test case for retrieveInventoryCount
     *
     * RetrieveInventoryCount
     *
     */
    public function test_retrieveInventoryCount() {
      $this->physicalCountInventoryChange(self::$catalog_object_id, self::$location_id);

      $counts = self::$api_instance->retrieveInventoryCount(self::$catalog_object_id);
      $this->assertInstanceOf(
          '\SquareConnect\Model\RetrieveInventoryCountResponse',
          $counts
      );
      $this->assertGreaterThanOrEqual(1, count($counts->getCounts()));
      $this->assertEquals(self::$catalog_object_id, $counts->getCounts()[0]->getCatalogObjectId());
    }
    /**
     * Test case for retrieveInventoryPhysicalCount
     *
     * RetrieveInventoryPhysicalCount
     *
     */
    public function test_retrieveInventoryPhysicalCount() {
      $this->physicalCountInventoryChange(self::$catalog_object_id, self::$location_id);

      $batch_retrieve_change_body = new \SquareConnect\Model\BatchRetrieveInventoryChangesRequest([
          "catalog_object_ids" => [ self::$catalog_object_id ],
          "types" => [ "PHYSICAL_COUNT" ]
      ]);
      $retrieve_response = self::$api_instance->batchRetrieveInventoryChanges($batch_retrieve_change_body);

      $physical_count = $retrieve_response->getChanges()[0]->getPhysicalCount();
      $count = self::$api_instance->retrieveInventoryPhysicalCount($physical_count->getId());
      $this->assertInstanceOf(
          '\SquareConnect\Model\RetrieveInventoryPhysicalCountResponse',
          $count
      );
      $this->assertEquals($physical_count, $count->getCount());
    }

    private function physicalCountInventoryChange($catalog_object_id, $location_id) {
      $batch_change_inventory_body = new \SquareConnect\Model\BatchChangeInventoryRequest([
          "idempotency_key" => uniqid(),
          "changes" => [
            [
              "type" => "PHYSICAL_COUNT",
              "physical_count" => [
                "catalog_object_id" => $catalog_object_id,
                "state" => "IN_STOCK",
                "location_id" => $location_id,
                "quantity" => "1",
                "occurred_at" => date("Y-m-d\TH:i:s\Z", time())
              ]
            ]
          ]
      ]);

      self::$api_instance->batchChangeInventory($batch_change_inventory_body);
    }
}
