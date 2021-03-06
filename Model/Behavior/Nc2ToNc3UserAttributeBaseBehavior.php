<?php
/**
 * Nc2ToNc3UserAttributeBaseBehavior
 *
 * @copyright Copyright 2014, NetCommons Project
 * @author Kohei Teraguchi <kteraguchi@commonsnet.org>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

App::uses('Nc2ToNc3BaseBehavior', 'Nc2ToNc3.Model/Behavior');

/**
 * Nc2ToNc3UserAttributeBaseBehavior
 *
 */
class Nc2ToNc3UserAttributeBaseBehavior extends Nc2ToNc3BaseBehavior {

/**
 * Nc2Item constant.
 *
 * @var array
 */
	private $__nc2ItemConstants = null;

/**
 * Nc2ItemDesc description.
 *
 * @var array
 */
	private $__nc2ItemDescriptions = null;

/**
 * Nc2Config autoregist_use_items.
 *
 * @var array
 */
	private $__nc2AutoregistUseItems = null;

/**
 * Nc3UserAttributeSetting weight.
 *
 * @var int
 */
	private $__userAttributeSettingWeight = null;

/**
 * Get Nc2Item value by constant.
 *
 * @param Model $model Model using this behavior.
 * @param string $constant Nc2Item item_name.
 * @param string $languageId Nc2Language id.
 * @return string Nc2Item value.
 */
	public function getNc2ItemValueByConstant(Model $model, $constant, $languageId) {
		return $this->_getNc2ItemValueByConstant($constant, $languageId);
	}

/**
 * Get Nc2ItemDesc description by id.
 *
 * @param Model $model Model using this behavior.
 * @param string $itemId Nc2Item item_id.
 * @return string Nc2ItemDesc description.
 */
	public function getNc2ItemDescriptionById(Model $model, $itemId) {
		return $this->_getNc2ItemDescriptionById($itemId);
	}

/**
 * Check Nc2Config autoregist_use_items.
 *
 * @param Model $model Model using this behavior.
 * @param string $itemId Nc2Item item_id.
 * @return bool True if data is Nc2Config autoregist_use_items.
 */
	public function isNc2AutoregistUseItem(Model $model, $itemId) {
		return $this->_isNc2AutoregistUseItem($itemId);
	}

/**
 * Check require Nc2Config autoregist_use_items.
 *
 * @param Model $model Model using this behavior.
 * @param string $itemId Nc2Item item_id.
 * @return bool True if data require as Nc2Config autoregist_use_items.
 */
	public function isNc2AutoregistUseItemRequire(Model $model, $itemId) {
		return $this->_isNc2AutoregistUseItemRequire($itemId);
	}

/**
 * Get Nc3UserAttributeSetting row.
 *
 * @param Model $model Model using this behavior.
 * @return string Nc3UserAttributeSetting row.
 */
	public function getUserAttributeSettingRow(Model $model) {
		return $this->_getUserAttributeSettingRow();
	}

/**
 * Get Nc3UserAttributeSetting col.
 *
 * @param Model $model Model using this behavior.
 * @return string Nc3UserAttributeSetting col.
 */
	public function getUserAttributeSettingCol(Model $model) {
		return $this->_getUserAttributeSettingCol();
	}

/**
 * Get Nc3UserAttributeSetting weight.
 *
 * @param Model $model Model using this behavior.
 * @return int Nc3UserAttributeSetting weight.
 */
	public function getUserAttributeSettingWeight(Model $model) {
		return $this->_getUserAttributeSettingWeight();
	}

/**
 * Increment Nc3UserAttributeSetting weight.
 *
 * @param Model $model Model using this behavior.
 * @return void
 */
	public function incrementUserAttributeSettingWeight(Model $model) {
		return $this->_incrementUserAttributeSettingWeight();
	}

/**
 * Get map
 *
 * @param array|string $nc2ItemIds Nc2Item item_id.
 * @return array Map data with Nc2Item item_id as key.
 */
	protected function _getMap($nc2ItemIds = null) {
		/* @var $Nc2ToNc3Map Nc2ToNc3Map */
		/* @var $UserAttribute UserAttribute */
		$Nc2ToNc3Map = ClassRegistry::init('Nc2ToNc3.Nc2ToNc3Map');
		$UserAttribute = ClassRegistry::init('UserAttributes.UserAttribute');

		$mapIdList = $Nc2ToNc3Map->getMapIdList('UserAttribute', $nc2ItemIds);
		$query = [
			'fields' => [
				'UserAttribute.id',
				'UserAttribute.key',
				'UserAttributeSetting.data_type_key',
			],
			'conditions' => [
				'UserAttribute.id' => $mapIdList
			],
			'recursive' => 1
		];
		$hasManyOptions = $UserAttribute->hasMany['UserAttributeChoice'];
		$UserAttribute->hasMany['UserAttributeChoice']['fields'] = [
			'UserAttributeChoice.name',
			'UserAttributeChoice.code',
		];
		$userAttributes = $UserAttribute->find('all', $query);
		$UserAttribute->hasMany['UserAttributeChoice'] = $hasManyOptions;
		if (!$userAttributes) {
			return $userAttributes;
		}

		$map = [];
		foreach ($userAttributes as $userAttribute) {
			$nc2Id = array_search($userAttribute['UserAttribute']['id'], $mapIdList);
			$map[$nc2Id] = $userAttribute;
		}

		if (is_string($nc2ItemIds)) {
			$map = $map[$nc2ItemIds];
		}

		return $map;
	}

/**
 * Initialize id map
 *
 * @return void
 */
	protected function _initializeIdMap() {
		/* @var $Nc2ToNc3Map Nc2ToNc3Map */
		/* @var $UserAttribute UserAttribute */
		$Nc2ToNc3Map = ClassRegistry::init('Nc2ToNc3.Nc2ToNc3Map');
		$UserAttribute = ClassRegistry::init('UserAttributes.UserAttribute');

		$mapIdList = $Nc2ToNc3Map->getMapIdList('UserAttribute');
		$query = [
			'fields' => [
				'UserAttribute.id'
			],
			'conditions' => [
				'UserAttribute.id' => $mapIdList
			],
			'recursive' => -1
		];
		$nc3Ids = $UserAttribute->find('list', $query);
		$deleteIds = array_diff($mapIdList, $nc3Ids);
		if ($deleteIds) {
			//$Nc2ToNc3Map->deleteAll();
		}
	}

/**
 * Get Nc2Item value by constant.
 *
 * @param string $constant Nc2Item item_name.
 * @param string $languageId Nc2 language id.
 * @return string Nc2Item value.
 */
	protected function _getNc2ItemValueByConstant($constant, $languageId) {
		if (!isset($this->__nc2ItemConstants)) {
			$this->__setNc2ItemConstants();
		}

		return Hash::get($this->__nc2ItemConstants, [$constant, $languageId], $constant);
	}

/**
 * Get Nc2ItemDesc description by id.
 *
 * @param string $itemId Nc2Item item_id.
 * @return string Nc2ItemDesc description.
 */
	protected function _getNc2ItemDescriptionById($itemId) {
		if (!isset($this->__nc2ItemDescriptions)) {
			/* @var $Nc2ItemsDesc AppModel */
			$Nc2ItemsDesc = $this->_getNc2Model('items_desc');
			$query = [
				'fields' => [
					'Nc2ItemsDesc.item_id',
					'Nc2ItemsDesc.description'
				],
				'recursive' => -1
			];
			$this->__nc2ItemDescriptions = $Nc2ItemsDesc->find('list', $query);
		}

		return Hash::get($this->__nc2ItemDescriptions, [$itemId], '');
	}

/**
 * Check Nc2Config autoregist_use_items
 *
 * @param string $itemId Nc2Item item_id.
 * @return bool True if data is Nc2Config autoregist_use_items.
 */
	protected function _isNc2AutoregistUseItem($itemId) {
		if (!isset($this->__nc2AutoregistUseItems)) {
			$this->__setNc2AutoregistUseItems();
		}

		return isset($this->_isNc2AutoregistUseItems[$itemId]);
	}

/**
 * Check require Nc2Config autoregist_use_items.
 *
 * @param string $itemId Nc2Item item_id.
 * @return bool True if data require as Nc2Config autoregist_use_items.
 */
	protected function _isNc2AutoregistUseItemRequire($itemId) {
		if (!isset($this->__nc2AutoregistUseItems)) {
			$this->__setNc2AutoregistUseItems();
		}

		$isRequire = (
			isset($this->_isNc2AutoregistUseItems[$itemId]) &&
			$this->_isNc2AutoregistUseItems[$itemId] == '1'
		);

		return $isRequire;
	}

/**
 * Get Nc3UserAttributeSetting row.
 *
 * @return string Nc3UserAttributeSetting row.
 */
	protected function _getUserAttributeSettingRow() {
		// 1行目
		return '1';
	}

/**
 * Get Nc3UserAttributeSetting col.
 *
 * @return string Nc3UserAttributeSetting col.
 */
	protected function _getUserAttributeSettingCol() {
		// 2列目
		return '2';
	}

/**
 * Get Nc3UserAttributeSetting weight.
 *
 * @return int Nc3UserAttributeSetting weight.
 */
	protected function _getUserAttributeSettingWeight() {
		if (!isset($this->__userAttributeSettingWeight)) {
			$this->__setUserAttributeSettingWeight();
		}

		return $this->__userAttributeSettingWeight;
	}

/**
 * Increment Nc3UserAttributeSetting weight.
 *
 * @return void
 */
	protected function _incrementUserAttributeSettingWeight() {
		if (!isset($this->__userAttributeSettingWeight)) {
			$this->__setUserAttributeSettingWeight();
		}

		$this->__userAttributeSettingWeight++;
	}

/**
 * Set Nc2Item constant.
 *
 * @return void
 */
	private function __setNc2ItemConstants() {
		/* @var $Language Language */
		$Language = ClassRegistry::init('M17n.Language');
		$query = [
			'fields' => [
				'Language.code',
				'Language.id'
			],
			'recursive' => -1
		];
		$language = $Language->find('list', $query);

		$this->__nc2ItemConstants = [
			'USER_ITEM_LOGIN' => [
				$language['ja'] => 'ログインID',
				$language['en'] => 'ID',
			],
			'USER_ITEM_PASSWORD' => [
				$language['ja'] => 'パスワード',
				$language['en'] => 'Password',
			],
			'USER_ITEM_USER_NAME' => [
				$language['ja'] => '会員氏名',
				$language['en'] => 'Name',
			],
			'USER_ITEM_HANDLE' => [
				$language['ja'] => 'ハンドル',
				$language['en'] => 'Handle',
			],
			'USER_ITEM_LANG_DIRNAME' => [
				$language['ja'] => '言語',
				$language['en'] => 'Language',
			],
			'USER_ITEM_TIMEZONE_OFFSET' => [
				$language['ja'] => 'タイムゾーン',
				$language['en'] => 'TimeZone',
			],
			'USER_ITEM_AVATAR' => [
				$language['ja'] => 'アバター',
				$language['en'] => 'Avatar',
			],
			'USER_ITEM_PROFILE' => [
				$language['ja'] => 'プロフィール',
				$language['en'] => 'Profile',
			],
			'USER_ITEM_EMAIL' => [
				$language['ja'] => 'eメール',
				$language['en'] => 'E-mail',
			],
			'USER_ITEM_MOBILE_EMAIL' => [
				$language['ja'] => '携帯メール',
				$language['en'] => 'Mobile mail',
			],
			'USER_ITEM_GENDER' => [
				$language['ja'] => '性別',
				$language['en'] => 'Sex',
			],
			'USER_ITEM_COUNTRY_CODE' => [
				$language['ja'] => '国名',
				$language['en'] => 'Nationality',
			],
			'USER_ITEM_ADDRESS' => [
				$language['ja'] => '住所',
				$language['en'] => 'Location',
			],
			'USER_ITEM_FAVORITE' => [
				$language['ja'] => '趣味',
				$language['en'] => 'Interest',
			],
			'USER_ITEM_GENDER_MAN' => [
				$language['ja'] => '男',
				$language['en'] => 'Interest',
			],
			'USER_ITEM_GENDER_WOMAN' => [
				$language['ja'] => '女',
				$language['en'] => 'Female',
			],
		];

		$this->__mergeNc2ItemConstantsItemFile();
	}

/**
 * Merge Nc2Item constant.
 *
 * @return void
 */
	private function __mergeNc2ItemConstantsItemFile() {
		/* @var $Nc2ToNc3 Nc2ToNc3 */
		$Nc2ToNc3 = ClassRegistry::init('Nc2ToNc3.Nc2ToNc3');
		$itemsIniPath = Hash::get($Nc2ToNc3->data, ['Nc2ToNc3', 'items_ini_path']);
		if (!$itemsIniPath) {
			return;
		}

		// TODOーitems.iniから定数を取得しマージ
		$nc2ItemConstants = $itemsIniPath;
		array_merge_recursive($this->__nc2ItemConstants, $nc2ItemConstants);
	}

/**
 * Set Nc2Config autoregist_use_items.
 *
 * @return void
 */
	private function __setNc2AutoregistUseItems() {
		/* @var $Nc2Config AppModel */
		$Nc2Config = $this->_getNc2Model('config');
		$autoregistUseItems = $Nc2Config->findByConfName('autoregist_use_items', 'conf_value', null, -1);
		$autoregistUseItems = explode('|', $autoregistUseItems['Nc2Config']['conf_value']);
		if (!end($autoregistUseItems)) {
			array_pop($autoregistUseItems);
		}
		$this->__nc2AutoregistUseItems = [];
		foreach ($autoregistUseItems as $autoregistUseItem) {
			list($itemId, $isRequired) = explode(':', $autoregistUseItem);
			$this->__nc2AutoregistUseItems[$itemId] = $isRequired;
		}
	}

/**
 * Set Nc3UserAttributeSetting weight.
 *
 * @return void
 */
	private function __setUserAttributeSettingWeight() {
		/* @var $UserAttribute UserAttribute */
		$UserAttribute = ClassRegistry::init('UserAttributes.UserAttribute');
		$this->__userAttributeSettingWeight = $UserAttribute->UserAttributeSetting->getMaxWeight(
			$this->_getUserAttributeSettingRow(),
			$this->_getUserAttributeSettingCol()
		);
		$this->_incrementUserAttributeSettingWeight();
	}

}
