# LoyaltyEvent

### Description

Provides information about a loyalty event.  For more information, see [Loyalty events](/docs/loyalty-api/overview/#loyalty-events).

## Properties
Name | Getter | Setter | Type | Description | Notes
------------ | ------------- | ------------- | ------------- | ------------- | -------------
**id** | getId() | setId($value) | **string** | The Square-assigned ID of the loyalty event. | 
**type** | getType() | setType($value) | **string** | The type of the loyalty event. See [LoyaltyEventType](#type-loyaltyeventtype) for possible values | 
**created_at** | getCreatedAt() | setCreatedAt($value) | **string** | The timestamp when the event was created, in RFC 3339 format. | 
**accumulate_points** | getAccumulatePoints() | setAccumulatePoints($value) | [**\SquareConnect\Model\LoyaltyEventAccumulatePoints**](LoyaltyEventAccumulatePoints.md) | Provides metadata when the event &#x60;type&#x60; is &#x60;ACCUMULATE_POINTS&#x60;. | [optional] 
**create_reward** | getCreateReward() | setCreateReward($value) | [**\SquareConnect\Model\LoyaltyEventCreateReward**](LoyaltyEventCreateReward.md) | Provides metadata when the event &#x60;type&#x60; is &#x60;CREATE_REWARD&#x60;. | [optional] 
**redeem_reward** | getRedeemReward() | setRedeemReward($value) | [**\SquareConnect\Model\LoyaltyEventRedeemReward**](LoyaltyEventRedeemReward.md) | Provides metadata when the event &#x60;type&#x60; is &#x60;REDEEM_REWARD&#x60;. | [optional] 
**delete_reward** | getDeleteReward() | setDeleteReward($value) | [**\SquareConnect\Model\LoyaltyEventDeleteReward**](LoyaltyEventDeleteReward.md) | Provides metadata when the event &#x60;type&#x60; is &#x60;DELETE_REWARD&#x60;. | [optional] 
**adjust_points** | getAdjustPoints() | setAdjustPoints($value) | [**\SquareConnect\Model\LoyaltyEventAdjustPoints**](LoyaltyEventAdjustPoints.md) | Provides metadata when the event &#x60;type&#x60; is &#x60;ADJUST_POINTS&#x60;. | [optional] 
**loyalty_account_id** | getLoyaltyAccountId() | setLoyaltyAccountId($value) | **string** | The ID of the &#x60;loyalty account&#x60; in which the event occurred. | 
**location_id** | getLocationId() | setLocationId($value) | **string** | The ID of the &#x60;location&#x60; where the event occurred. | [optional] 
**source** | getSource() | setSource($value) | **string** | Defines whether the event was generated by the Square Point of Sale. See [LoyaltyEventSource](#type-loyaltyeventsource) for possible values | 
**expire_points** | getExpirePoints() | setExpirePoints($value) | [**\SquareConnect\Model\LoyaltyEventExpirePoints**](LoyaltyEventExpirePoints.md) | Provides metadata when the event &#x60;type&#x60; is &#x60;EXPIRE_POINTS&#x60;. | [optional] 
**other_event** | getOtherEvent() | setOtherEvent($value) | [**\SquareConnect\Model\LoyaltyEventOther**](LoyaltyEventOther.md) | Provides metadata when the event &#x60;type&#x60; is &#x60;OTHER&#x60;. | [optional] 

Note: All properties are protected and only accessed via getters and setters.

[[Back to Model list]](../../README.md#documentation-for-models) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to README]](../../README.md)

