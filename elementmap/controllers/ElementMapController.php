<?php

namespace Craft;

class ElementMapController extends BaseController {

	/**
	 * Retrieves relationships in a given direction from a set of elements.
	 * @param elementids A list of element IDs to find relations against.
	 * @param astarget If set to true, the element IDs provided are of the target elements, otherwise, they are of the source elements.
	 */
	private function getRelationshipGroups($elementids, $astarget) {

		if ($astarget) {
			$fromcol = 'targetId';
			$tocol = 'sourceId';
		} else {
			$fromcol = 'sourceId';
			$tocol = 'targetId';
		}

		// Get a list of elements where the given entry ID is part of the relationship, either target or source, defined by `astarget`.
		$conditions = array(
			'and',
			array(
				'in',
				$fromcol,
				$elementids
			),
			array(
				'or',
				'sourceLocale is null',
				'sourceLocale = :sourceLocale',
			),
		);
		$params = array(
			':sourceLocale' => craft()->locale->id,
		);
		$results = craft()->db->createCommand()
			->select('r.' . $tocol . ' AS id, e.type AS type')
			->from('relations r')
			->join('elements e', 'r.' . $tocol . ' = e.id')
			->where($conditions, $params)
			->queryAll();
		
		// Create element type groups in order to further process the element list.
		$elements = array(
			'MatrixBlock' => array(),
			'SuperTable_Block' => array(),
			'Entry' => array(),
			'GlobalSet' => array(),
			'Category' => array(),
			'Tag' => array(),
			'Asset' => array(),
			'Other' => array(),
			'results' => array(),
		);
		$this->integrateGroupData($elements, $results);
		return $elements;
	}

	/**
	 * Processes a set of element types/ids retrieved, and sorts them into appropriate containers within a grouping object.
	 * @param groups A reference to the groups container to store elements within.
	 * @param elements The list of elements to store within the container.
	 */
	private function integrateGroupData(&$groups, &$elements) {
		foreach ($elements as $element) {
			if (isset($groups[$element['type']])) {
				$groups[$element['type']][] = $element['id'];
			} else {
				$groups['Other'][] = $element['id'];
			}
		}
	}

	/**
	 * Retrieves a list of matrix block IDs based on the given set of owner ids.
	 * @param owners Retrieve all matrix blocks that are owned by any of the owners provided.
	 */
	private function getMatrixBlocksByOwners($owners) {
		$conditions = array(
			'in',
			'ownerId',
			$owners,
		);
		return craft()->db->createCommand()
			->select('id')
			->from('matrixblocks')
			->where($conditions)
			->queryColumn();
	}

	/**
	 * Retrieves a list of supertable block IDs based on the given set of owner ids.
	 * @param owners Retrieve all supertable blocks that are owned by any of the owners provided
	 */
	private function getSuperTableBlocksByOwners($owners) {
		$conditions = array(
			'in',
			'ownerId',
			$owners,
		);
		return craft()->db->createCommand()
			->select('id')
			->from('supertableblocks')
			->where($conditions)
			->queryColumn();
	}

	/**
	 * Finds owner elements of supertable group items, and returns those elements.
	 * @param group A list of supertable block ids to find owners for.
	 */
	private function processSuperTableGroup($group) {
		$conditions = array(
			'in',
			'stb.id',
			$group,
		);
		return craft()->db->createCommand()
			->select('e.id AS id, e.type AS type')
			->from('supertableblocks stb')
			->join('elements e', 'stb.ownerId = e.id')
			->where($conditions)
			->queryAll();
	}

	/**
	 * Finds owner elements of matrix group items, and returns those elements.
	 * @param group A list of matrix block ids to find owners for.
	 */
	private function processMatrixGroup($group) {
		$conditions = array(
			'in',
			'mb.id',
			$group,
		);
		return craft()->db->createCommand()
			->select('e.id AS id, e.type AS type')
			->from('matrixblocks mb')
			->join('elements e', 'mb.ownerId = e.id')
			->where($conditions)
			->queryAll();
	}

	/**
	 * Converts entries into a list of standardized result items.
	 * @param group The IDs of the entries to convert.
	 */
	private function processEntryGroup($group) {
		$criteria = craft()->elements->getCriteria(ElementType::Entry);
		$criteria->id = $group;
		$elements = $criteria->findAll();

		$results = array();
		foreach ($elements as $element) {
			$results[] = array(
				'id' => $element->id,
				'type' => 'entry',
				'title' => $element->title,
				'url' => $element->cpEditUrl,
			);
		}
		return $results;
	}

	/**
	 * Converts global set items into a list of standardized result items.
	 * @param group The IDs of the global sets to convert.
	 */
	private function processGlobalSetGroup($group) {
		$criteria = craft()->elements->getCriteria(ElementType::GlobalSet);
		$criteria->id = $group;
		$elements = $criteria->findAll();

		$results = array();
		foreach ($elements as $element) {
			$results[] = array(
				'id' => $element->id,
				'type' => 'globalset',
				'title' => $element->name,
				'url' => $element->cpEditUrl,
			);
		}
		return $results;
	}

	/**
	 * Converts categories into a list of standardized result items.
	 * @param group The IDs of the categories to convert.
	 */
	private function processCategoryGroup($group) {
		$criteria = craft()->elements->getCriteria(ElementType::Category);
		$criteria->id = $group;
		$elements = $criteria->findAll();

		$results = array();
		foreach ($elements as $element) {
			$results[] = array(
				'id' => $element->id,
				'type' => 'category',
				'title' => $element->title,
				'url' => $element->cpEditUrl,
			);
		}
		return $results;
	}

	/**
	 * Converts tags into a list of standardized result items.
	 * @param group The IDs of the tags to convert.
	 */
	private function processTagGroup($group) {
		$criteria = craft()->elements->getCriteria(ElementType::Tag);
		$criteria->id = $group;
		$elements = $criteria->findAll();

		$results = array();
		foreach ($elements as $element) {
			$results[] = array(
				'id' => $element->id,
				'type' => 'tag',
				'title' => $element->title,
				'url' => '/' . craft()->config->get('cpTrigger') . '/settings/tags/' . $element->groupId,
			);
		}
		return $results;
	}

	/**
	 * Converts assets into a list of standardized result items.
	 * @param group The IDs of the assets to convert.
	 */
	private function processAssetGroup($group) {
		$criteria = craft()->elements->getCriteria(ElementType::Asset);
		$criteria->id = $group;
		$elements = $criteria->findAll();

		$results = array();
		foreach ($elements as $element) {
			$results[] = array(
				'id' => $element->id,
				'type' => 'asset',
				'title' => $element->title,
				'url' => $element->url,
			);
		}
		return $results;
	}

	/**
	 * Iterates over elements within each group, converting what it can find into result sets.
	 * @param groups A reference to the groups container that contains the processed and unprocessed elements.
	 */
	private function processRelationshipGroups(&$groups) {
		if (count($groups['SuperTable_Block'])) {
			$data = $this->processSuperTableGroup($groups['SuperTable_Block']); // Process the data for this group.
			$groups['SuperTable_Block'] = array(); // Clear the data for this group.
			$this->integrateGroupData($groups, $data); // Re-integrate new data into the group container.
			$this->processRelationshipGroups($groups); // Process more groups.
		} else if (count($groups['MatrixBlock'])) {
			$data = $this->processMatrixGroup($groups['MatrixBlock']); // Process the data for this group.
			$groups['MatrixBlock'] = array(); // Clear the data for this group.
			$this->integrateGroupData($groups, $data); // Re-integrate new data into the group container.
			$this->processRelationshipGroups($groups); // Process more groups.
		} else if (count($groups['Entry'])) {
			$data = $this->processEntryGroup($groups['Entry']); // Process the data for this group.
			$groups['Entry'] = array(); // Clear the data for this group.
			$groups['results'] = array_merge($groups['results'], $data); // Add the results to the set.
			$this->processRelationshipGroups($groups); // Process more groups.
		} else if (count($groups['GlobalSet'])) {
			$data = $this->processGlobalSetGroup($groups['GlobalSet']); // Process the data for this group.
			$groups['GlobalSet'] = array(); // Clear the data for this group.
			$groups['results'] = array_merge($groups['results'], $data); // Add the results to the set.
			$this->processRelationshipGroups($groups); // Process more groups.
		} else if (count($groups['Category'])) {
			$data = $this->processCategoryGroup($groups['Category']); // Process the data for this group.
			$groups['Category'] = array(); // Clear the data for this group.
			$groups['results'] = array_merge($groups['results'], $data); // Add the results to the set.
			$this->processRelationshipGroups($groups); // Process more groups.
		} else if (count($groups['Tag'])) {
			$data = $this->processTagGroup($groups['Tag']); // Process the data for this group.
			$groups['Tag'] = array(); // Clear the data for this group.
			$groups['results'] = array_merge($groups['results'], $data); // Add the results to the set.
			$this->processRelationshipGroups($groups); // Process more groups.
		} else if (count($groups['Asset'])) {
			$data = $this->processAssetGroup($groups['Asset']); // Process the data for this group.
			$groups['Asset'] = array(); // Clear the data for this group.
			$groups['results'] = array_merge($groups['results'], $data); // Add the results to the set.
			$this->processRelationshipGroups($groups); // Process more groups.
		}
	}

	/**
	 * Finds related elements to a given element, and returns a map indicating the relationships that the element has to others.
	 */
	public function actionGetElementMap() {
		$entryid = intval(craft()->request->getRequiredParam('id')); // Retrieve the ID of the element to find relationships for.

		// Find incoming relationships. Start at this entry, check everything that references it, then trace those owners back up to meaningful elements.
		// This means if a matrix has a related entries field, the matrix will be the owner. This will trace each element's owner back up to
		// to some root element such as an entry, category, or asset.
		$fromdata = $this->getRelationshipGroups(array($entryid), true);
		$this->processRelationshipGroups($fromdata);

		// Find outgoing relationships. Start at this entry, then get all of its inner elements that may own data.
		// Just as above, an element referenced by a matrix block won't actually have a connection to the entry that contains that
		// matrix block, just the block itself. For any kind of element that can own others, trace their children and look at their data
		// to gather all relationship information.
		$toset_1 = array($entryid); // Entry.
		$toset_2 = $this->getMatrixBlocksByOwners($toset_1); // Entry -> matrix blocks.
		$toset_3 = $this->getSuperTableBlocksByOwners($toset_2); // Entry -> matrix blocks -> supertable blocks.
		$toset_4 = $this->getSuperTableBlocksByOwners($toset_1); // Entry -> supertable blocks.
		$toset_5 = $this->getMatrixBlocksByOwners($toset_4); // Entry -> supertable blocks -> matrix blocks.
		$todata = $this->getRelationshipGroups(array_merge($toset_1, $toset_2, $toset_3, $toset_4, $toset_5), false);
		$this->processRelationshipGroups($todata);

		// Create and return a result object.
		$results = array(
			'from' => $fromdata['results'],
			'to' => $todata['results'],
		);
		
		$this->returnJson($results);
	}
}
