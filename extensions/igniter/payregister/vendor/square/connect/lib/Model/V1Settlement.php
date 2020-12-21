<?php
/**
 * NOTE: This class is auto generated by the swagger code generator program.
 * https://github.com/swagger-api/swagger-codegen
 * Do not edit the class manually.
 */

namespace SquareConnect\Model;

use \ArrayAccess;
/**
 * V1Settlement Class Doc Comment
 *
 * @category Class
 * @package  SquareConnect
 * @author   Square Inc.
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache License v2
 * @link     https://squareup.com/developers
 */
class V1Settlement implements ArrayAccess
{
    /**
      * Array of property to type mappings. Used for (de)serialization 
      * @var string[]
      */
    static $swaggerTypes = array(
        'id' => 'string',
        'status' => 'string',
        'total_money' => '\SquareConnect\Model\V1Money',
        'initiated_at' => 'string',
        'bank_account_id' => 'string',
        'entries' => '\SquareConnect\Model\V1SettlementEntry[]'
    );
  
    /** 
      * Array of attributes where the key is the local name, and the value is the original name
      * @var string[] 
      */
    static $attributeMap = array(
        'id' => 'id',
        'status' => 'status',
        'total_money' => 'total_money',
        'initiated_at' => 'initiated_at',
        'bank_account_id' => 'bank_account_id',
        'entries' => 'entries'
    );
  
    /**
      * Array of attributes to setter functions (for deserialization of responses)
      * @var string[]
      */
    static $setters = array(
        'id' => 'setId',
        'status' => 'setStatus',
        'total_money' => 'setTotalMoney',
        'initiated_at' => 'setInitiatedAt',
        'bank_account_id' => 'setBankAccountId',
        'entries' => 'setEntries'
    );
  
    /**
      * Array of attributes to getter functions (for serialization of requests)
      * @var string[]
      */
    static $getters = array(
        'id' => 'getId',
        'status' => 'getStatus',
        'total_money' => 'getTotalMoney',
        'initiated_at' => 'getInitiatedAt',
        'bank_account_id' => 'getBankAccountId',
        'entries' => 'getEntries'
    );
  
    /**
      * $id The settlement's unique identifier.
      * @var string
      */
    protected $id;
    /**
      * $status The settlement's current status. See [V1SettlementStatus](#type-v1settlementstatus) for possible values
      * @var string
      */
    protected $status;
    /**
      * $total_money The amount of money involved in the settlement. A positive amount indicates a deposit, and a negative amount indicates a withdrawal. This amount is never zero.
      * @var \SquareConnect\Model\V1Money
      */
    protected $total_money;
    /**
      * $initiated_at The time when the settlement was submitted for deposit or withdrawal, in ISO 8601 format.
      * @var string
      */
    protected $initiated_at;
    /**
      * $bank_account_id The Square-issued unique identifier for the bank account associated with the settlement.
      * @var string
      */
    protected $bank_account_id;
    /**
      * $entries The entries included in this settlement.
      * @var \SquareConnect\Model\V1SettlementEntry[]
      */
    protected $entries;

    /**
     * Constructor
     * @param mixed[] $data Associated array of property value initializing the model
     */
    public function __construct(array $data = null)
    {
        if ($data != null) {
            if (isset($data["id"])) {
              $this->id = $data["id"];
            } else {
              $this->id = null;
            }
            if (isset($data["status"])) {
              $this->status = $data["status"];
            } else {
              $this->status = null;
            }
            if (isset($data["total_money"])) {
              $this->total_money = $data["total_money"];
            } else {
              $this->total_money = null;
            }
            if (isset($data["initiated_at"])) {
              $this->initiated_at = $data["initiated_at"];
            } else {
              $this->initiated_at = null;
            }
            if (isset($data["bank_account_id"])) {
              $this->bank_account_id = $data["bank_account_id"];
            } else {
              $this->bank_account_id = null;
            }
            if (isset($data["entries"])) {
              $this->entries = $data["entries"];
            } else {
              $this->entries = null;
            }
        }
    }
    /**
     * Gets id
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
  
    /**
     * Sets id
     * @param string $id The settlement's unique identifier.
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    /**
     * Gets status
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }
  
    /**
     * Sets status
     * @param string $status The settlement's current status. See [V1SettlementStatus](#type-v1settlementstatus) for possible values
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }
    /**
     * Gets total_money
     * @return \SquareConnect\Model\V1Money
     */
    public function getTotalMoney()
    {
        return $this->total_money;
    }
  
    /**
     * Sets total_money
     * @param \SquareConnect\Model\V1Money $total_money The amount of money involved in the settlement. A positive amount indicates a deposit, and a negative amount indicates a withdrawal. This amount is never zero.
     * @return $this
     */
    public function setTotalMoney($total_money)
    {
        $this->total_money = $total_money;
        return $this;
    }
    /**
     * Gets initiated_at
     * @return string
     */
    public function getInitiatedAt()
    {
        return $this->initiated_at;
    }
  
    /**
     * Sets initiated_at
     * @param string $initiated_at The time when the settlement was submitted for deposit or withdrawal, in ISO 8601 format.
     * @return $this
     */
    public function setInitiatedAt($initiated_at)
    {
        $this->initiated_at = $initiated_at;
        return $this;
    }
    /**
     * Gets bank_account_id
     * @return string
     */
    public function getBankAccountId()
    {
        return $this->bank_account_id;
    }
  
    /**
     * Sets bank_account_id
     * @param string $bank_account_id The Square-issued unique identifier for the bank account associated with the settlement.
     * @return $this
     */
    public function setBankAccountId($bank_account_id)
    {
        $this->bank_account_id = $bank_account_id;
        return $this;
    }
    /**
     * Gets entries
     * @return \SquareConnect\Model\V1SettlementEntry[]
     */
    public function getEntries()
    {
        return $this->entries;
    }
  
    /**
     * Sets entries
     * @param \SquareConnect\Model\V1SettlementEntry[] $entries The entries included in this settlement.
     * @return $this
     */
    public function setEntries($entries)
    {
        $this->entries = $entries;
        return $this;
    }
    /**
     * Returns true if offset exists. False otherwise.
     * @param  integer $offset Offset 
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }
  
    /**
     * Gets offset.
     * @param  integer $offset Offset 
     * @return mixed 
     */
    public function offsetGet($offset)
    {
        return $this->$offset;
    }
  
    /**
     * Sets value based on offset.
     * @param  integer $offset Offset 
     * @param  mixed   $value  Value to be set
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }
  
    /**
     * Unsets offset.
     * @param  integer $offset Offset 
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->$offset);
    }
  
    /**
     * Gets the string presentation of the object
     * @return string
     */
    public function __toString()
    {
        if (defined('JSON_PRETTY_PRINT')) {
            return json_encode(\SquareConnect\ObjectSerializer::sanitizeForSerialization($this), JSON_PRETTY_PRINT);
        } else {
            return json_encode(\SquareConnect\ObjectSerializer::sanitizeForSerialization($this));
        }
    }
}
