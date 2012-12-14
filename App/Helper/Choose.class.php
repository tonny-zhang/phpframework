<?php

class Helper_Choose
{
	function FormatChooseGet( $get )
	{
		$baseArg = array();

		if ( is_numeric( $get['cid'] ) )
			$baseArg['category'] = $get['cid'];

		if ( $get['kw'] )
			$baseArg['kw'] = trim( $get['kw'] );
		// --- 对kw中的空格进行处理
		if (strpos($baseArg['kw'], ' ') !== false || strpos($baseArg['kw'], '　') !== false) {
			$cleanKw = preg_split('/[ |　]/', $baseArg['kw']);
			$cleanKw = array_filter($cleanKw);
			$baseArg['kw'] = implode(' ', $cleanKw);
		}
		
		$baseArg['index'] = array();

		if ( $get['iid'] )
		{
			$indexIdList = array_filter( explode( '-', $get['iid'] ) );
			$indexList = array();

			$objPcIdxValue = Core::ImportMl('Product_IndexValue');
			foreach ( $indexIdList as $indexValueId )
			{
				$valueInfo = $objPcIdxValue->getPcIdxValue( $indexValueId );

				if ( $valueInfo )
					$indexList[$valueInfo['index_id']] = $valueInfo['id'];
			}

			$baseArg['index'] = $indexList;
		}

		if ( is_numeric( $get['bid'] ) )
			$baseArg['brand'] = ( int ) $get['bid'];

		if ( is_numeric( $get['sex'] ) )
			$baseArg['sex'] = intval( $get['sex'] );

		if ( is_numeric( $get['maxprice'] ) )
			$baseArg['price']['max'] = intval( $get['maxprice'] );

		if ( is_numeric( $get['minprice'] ) )
			$baseArg['price']['min'] = intval( $get['minprice'] );

		if ( is_numeric( $get['limit'] ) )
		{
			$baseArg['limit'] = intval( $get['limit'] );
		}
		else
			$baseArg['limit'] = 40;

		if ( is_numeric( $get['page'] ) )
		{
			$baseArg['page'] = intval( $get['page'] );
			$baseArg['offset'] = ( $baseArg['page'] - 1 ) * $baseArg['limit'];
		}
		else
		{
			$baseArg['page'] = 1;
			if ( is_numeric( $get['offset'] ) )
				$baseArg['offset'] = intval( $get['offset'] );
		}

		if ( is_numeric( $get['discount'] ) )
			$baseArg['discount'] = intval( $get['discount'] );

		if ( is_numeric( $get['shop'] ) )
			$baseArg['shop'] = intval( $get['shop'] );

		if ( $get['from'] )
			$baseArg['from'] = trim( $get['from'] );
		if ( is_numeric( $get['geturl'] ) )
			$baseArg['geturl'] = intval( $get['geturl'] );

		if ( is_numeric( $get['order'] ) )
			$baseArg['order'] = intval( $get['order'] );
		else
			$baseArg['order'] = 1;

		return $baseArg;
	}

	function MergeHotIndexCondition( $condition, $hotIndexList )
	{
		if ( $condition['index'] )
		{
			foreach ( $condition['index'] as $key => $val )
			{
				if ( $hotIndexList[$key] )
				{
					$tk = $key;
					$tv = $val;

					unset( $condition['index'][$key] );
				}
			}

			if ( $tv > 0 )
				$condition['index'][$tk] = $tv;
		}

		return $condition;
	}

	function UnsetHotIndexCondition( $condition, $hotIndexList )
	{
		if ( $condition['index'] )
		{
			foreach ( $condition['index'] as $key => $val )
			{
				if ( $hotIndexList[$key] )
				{
					unset( $condition['index'][$key] );
				}
			}
		}

		return $condition;
	}

	function FormatCategory( $firstCategoryList, $secondCategoryList, $statList, $baseArg, $categoryParentId )
	{
		$return = array();

		$firstCategoryList = self::FormatCategoryList( $firstCategoryList, $statList, $baseArg, $categoryParentId );
		$secondCategoryList = self::FormatCategoryList( $secondCategoryList, $statList, $baseArg, $categoryParentId, true );

		if ( $firstCategoryList )
			$return['level1'] = $firstCategoryList;

		if ( count( $secondCategoryList ) )
			$return['level2'] = $secondCategoryList;

		return $return;
	}

	function FormatCategoryList( $categoryList, $statList, $baseArg, $categoryParentId, $clean = false )
	{
		foreach ( $categoryList as $key => $val )
		{
			$count = ( int ) $statList[$val['id']];
			$categoryList[$key]['count'] = $count;

			if ( $clean && !$count )
			{
				unset( $categoryList[$key] );
				continue;
			}

			if ( $baseArg['sex'] && !$val['selector_sex'] )
			{
				unset( $categoryList[$key] );
				continue;
			}

			if ( $baseArg['sex'] && !$val['selector_sex'] )
			{
				unset( $categoryList[$key] );
				continue;
			}

			$arg = $baseArg;
			$arg['category'] = $val['id'];
			unset( $arg['index'] );

			$categoryList[$key]['link'] = self::GetChooseUrl( $arg );

			if ( $val['id'] == $baseArg['category'] || $val['id'] == $categoryParentId )
				$categoryList[$key]['on'] = 1;
		}

		return $categoryList;
	}

	function FormatSelector( $selectorIndexList, $condition, $baseArg, $disableSelectorValueList )
	{
		if ( !$selectorIndexList )
			return array();

		if ( !$disableSelectorValueList )
			$disableSelectorValueList = array();

		$objPcSearch = Core::ImportMl('Product_Search');

		$statResult = array();
		foreach ( $selectorIndexList as $indexId => $val )
		{
			$cond = $condition;
			unset( $cond['index'][$indexId] );

			$objPcSearch->SetSelect( 'index_stat', $cond );
			$tmpResult = $objPcSearch->GetResult();

			$statResult[$indexId] = $tmpResult['index_stat'][$indexId];
		}

		if ( !$statResult )
			return array();

		foreach ( $selectorIndexList as $key => $val )
		{
			if ( !$statResult[$val['id']] )
			{
				unset( $selectorIndexList[$key] );
				continue;
			}
		}

		foreach ( $selectorIndexList as $key => $val )
		{
			$valueList = unserialize( $val['setting'] );

			if ( !$valueList || !is_array( $valueList ) )
			{
				unset( $selectorIndexList[$key] );
				continue;
			}

			foreach ( $valueList as $k => $v )
			{
				$count = ( int ) $statResult[$val['id']][$v['id']];

				if ( !$count )
				{
					unset( $valueList[$k] );
					continue;
				}

				if ( in_array( $v['id'], $disableSelectorValueList ) )
				{
					unset( $valueList[$k] );
					continue;
				}

				$valueList[$k]['count'] = $count;

				$arg = $baseArg;
				$arg['index'] = self::AppendIndex( $v['id'], $arg['index'] );

				$valueList[$k]['link'] = self::GetChooseUrl( $arg );

				if ( is_array( $baseArg['index'] ) )
				{
					if ( in_array( $v['id'], $baseArg['index'] ) )
						$valueList[$k]['on'] = 1;
				}
			}

			if ( count( $valueList ) == 0 )
			{
				unset( $selectorIndexList[$key] );
				continue;
			}

			$selectorIndexList[$key]['value_list'] = $valueList;
		}

		return $selectorIndexList;
	}

	function AppendIndex( $valueId, $indexList )
	{
		if ( !is_array( $indexList ) )
			$indexList = array();

		if ( $valueId && !in_array( $valueId, $indexList ) )
			$indexList[] = $valueId;

		return $indexList;
	}

	function FormatIndex( $hotIndexList, $condition, $baseArg )
	{
		if ( !$hotIndexList )
			return array();

		$objPcSearch = Core::ImportMl('Product_Search');

		$statResult = array();

		$objPcSearch->SetSelect( 'index_stat', $condition );
		$statResult = $objPcSearch->GetResult();
		$statResult = $statResult['index_stat'];

		if ( !$statResult )
			return array(); 
		// 删除不在分类设定的index中的值
		foreach ( $statResult as $key => $value )
		{
			if ( !$hotIndexList[$key] )
				unset( $statResult[$key] );
		}

		if ( is_array( $statResult ) )
		{
			$objPcIdxValue = Core::ImportMl('Product_IndexValue');

			$mergeList = array();

			foreach ( $statResult as $key => $value )
			{
				$list = $objPcIdxValue->getPcIdxValueList( $key );

				foreach ( $value as $valueId => $count )
				{
					$list[$valueId]['count'] = $count;

					$arg = $baseArg;
					$arg['index'] = self::AppendIndex( $valueId, $arg['index'] );

					$list[$valueId]['link'] = self::GetChooseUrl( $arg );

					if ( is_array( $baseArg['index'] ) )
					{
						if ( in_array( $valueId, $baseArg['index'] ) )
							$list[$valueId]['on'] = 1;
					}

					$mergeList[] = $list[$valueId];
				}
			}
		} 
		// 排序
		if ( is_array( $mergeList ) )
		{
			$mergeList = Helper_Misc::arraySortByMultiCols( $mergeList, array( 'count' => SORT_DESC, 'order_id' => SORT_DESC ) );
		}

		if ( is_array( $mergeList ) )
			$mergeList = array_slice( $mergeList, 0, 12, true );
		else
			$mergeList = array();

		return $mergeList;
	}

	function FormatBrand( $statList, $baseArg )
	{
		if ( !$statList )
			return array();

		$brandBestList = array();
		$brandAllList = array();

		$objBrand = Core::ImportMl('Brand');

		$brandIdList = array_filter( array_keys( $statList ) );
		$brandAllList = $objBrand->getBrandList($brandIdList );

		if ( !$brandAllList )
			return array();

		$selectBrandId = ( int ) $baseArg['brand'];
		$firstBrandId = 0;

		$parentBrandList = $objBrand->getBrandParent();
		// 为brand添加上count和link
		foreach ( $brandAllList as $k => $val )
		{
			$brandAllList[$k]['count'] = ( int ) $statList[$val['id']];

			$arg = $baseArg;
			$arg['brand'] = $val['id'];

			$brandAllList[$k]['link'] = self::GetChooseUrl( $arg );

			if ( $val['id'] == $selectBrandId )
				$brandAllList[$k]['on'] = 1; 
			// 取出父品牌
			if ( $parentBrandList[$val['pid']] )
			{
				if ( !$brandAllList[$val['pid']] )
				{
					$arg = $baseArg;
					$arg['brand'] = $val['pid'];

					$brandAllList[$val['pid']] = $parentBrandList[$val['pid']];
					$brandAllList[$val['pid']]['link'] = self::GetChooseUrl( $arg );

					if ( $val['pid'] == $selectBrandId )
						$brandAllList[$val['pid']]['on'] = 1;
				}

				if ( $val['id'] == $selectBrandId )
					$firstBrandId = $val['pid'];

				$brandAllList[$val['pid']]['count'] += ( int ) $statList[$val['id']];
			}
		}

		if ( !$firstBrandId )
			$firstBrandId = $selectBrandId;

		$selectBrandInfo = $brandAllList[$firstBrandId];

		if ( $selectBrandInfo['is_parent'] )
		{
			$selectBrandInfo['child_list'] = array();

			$childList = $objBrand->getBrandChildList( $selectBrandInfo['id'] );

			foreach ( $childList as $val )
			{
				if ( $brandAllList[$val['id']] )
					$selectBrandInfo['child_list'][$val['id']] = $brandAllList[$val['id']];
			}
		} 
		// 按照分类设定好的排序
		$sortList = array();
		if ( $baseArg['category'] )
		{
			$objProductCategoryBrand = Core::ImportMl('Product_CategoryBrand');
			$sortList = $objProductCategoryBrand->getCategoryBrandList($baseArg['category']);
		}

		if ( $sortList )
		{
			foreach ( $sortList as $key => $val )
			{
				if ( $statList[$key] && $brandAllList[$val['id']] )
					$brandBestList[$val['id']] = $brandAllList[$val['id']];
			}

			$brandBestList = array_slice( $brandBestList, 0, 7, true );
		}

		foreach ( $brandAllList as $k => $val )
		{
			if ( $val['pid'] )
			{
				unset( $brandAllList[$k] );
			}
		}

		$brandAllList = Helper_Misc::arraySortByCol( $brandAllList, 'count', SORT_DESC );

		if ( !$brandBestList && is_array( $brandAllList ) )
		{
			$brandBestList = array_slice( $brandAllList, 0, 7, true );
			$brandBestList = ArrayIndex( $brandBestList, 'id' );
		}

		if ( $selectBrandInfo )
		{
			unset( $brandBestList[$selectBrandInfo['id']] );
			$brandBestList = array( $selectBrandInfo['id'] => $selectBrandInfo ) + $brandBestList;

			if ( is_array( $selectBrandInfo['child_list'] ) )
			{
				foreach ( $selectBrandInfo['child_list'] as $val )
				{
					unset( $brandBestList[$val['id']] );
				}
			}
		}

		$bestCount = count( $brandBestList );

		if ( $bestCount < 7 )
		{
			$bestCount = 7 - $bestCount;
			foreach ( $brandAllList as $val )
			{
				if ( is_array( $selectBrandInfo['child_list'] ) && $selectBrandInfo['child_list'][$val['id']] )
					continue;

				if ( !$brandBestList[$val['id']] )
					$brandBestList[$val['id']] = $val;

				$bestCount--;

				if ( !$bestCount )
					break;
			}
		}

		foreach ( $brandBestList as $key => $val )
		{
			if ( $val['pid'] && $brandBestList[$val['pid']] )
				unset( $brandBestList[$key] );
		}

		if ( is_array( $selectBrandInfo['child_list'] ) )
		{
			$sliceNum = 7 - count( $selectBrandInfo['child_list'] );
			$sliceNum = $sliceNum > 0 ? $sliceNum : 7;

			$brandBestList = array_slice( $brandBestList, 0, $sliceNum, true );
			$brandBestList = ArrayIndex( $brandBestList, 'id' );
		}

		$brandAllList = array_slice( $brandAllList, 0, 100, true );
		$brandAllList = Helper_Misc::arraySortByCol( $brandAllList, 'letter', SORT_ASC );

		$brandTableList = array();
		if ( count( $brandAllList ) == count( $brandBestList ) )
		{
			$brandAllList = array();
		}
		else
		{
			foreach ( $brandAllList as $key => $val )
			{
				$letter = ord( $val['letter'] );
				if ( $letter >= 97 && $letter <= 102 )
					$brandTableList['a'][$key] = $val;
				elseif ( $letter >= 103 && $letter <= 109 )
					$brandTableList['g'][$key] = $val;
				elseif ( $letter >= 110 && $letter <= 116 )
					$brandTableList['n'][$key] = $val;
				elseif ( $letter >= 117 && $letter <= 122 )
					$brandTableList['u'][$key] = $val;
				else
					$brandTableList['a'][$key] = $val;
			}
		}

		$return = array();
		if ( $brandTableList )
			$return['all'] = $brandTableList;

		if ( $brandBestList )
			$return['best'] = array_values( $brandBestList );

		return $return;
	}

	function FormatOrder( $baseArg )
	{
		$orderlist = array( 
			array( 'name' => '热门排行',
				'id' => '2', 
				),
			array( 'name' => '上货时间最近',
				'id' => '1', 
				),
			array( 'name' => '价格',
				'id' => '3',
				'arrow' => 'up', 
				),
			array( 'name' => '折扣',
				'id' => '4',
				'arrow' => 'down', 
				), 
			);

		foreach ( $orderlist as $key => $order )
		{
			if ( $order['id'] == $baseArg['order'] )
				$orderlist[$key]['on'] = 1;

			$arg = $baseArg;
			$arg['order'] = $order['id'];

			$orderlist[$key]['link'] = self::GetChooseUrl( $arg );
		}

		return $orderlist;
	}

	function GetChooseUrl( $arg )
	{
		$url = "";
		unset( $arg['limit'] );
		unset( $arg['offset'] );
		unset( $arg['page'] );

		foreach ( $arg as $key => $value )
		{
			switch ( $key )
			{
				case 'category' :
					$url .= '&cid=' . $value;
					break;
				case 'index' :
					if ( count( $value ) > 0 )
						$url .= '&iid=' . implode( '-', $value );
					break;
				case 'brand' :
					$url .= '&bid=' . $value;
					break;

				case 'kw' : 
					// $value = str_replace(' ','+',$value);
					$url .= '&kw=' . urlencode( $value );
					break;
				case 'price' :
					$url .= '&minprice=' . $value['min'];
					$url .= '&maxprice=' . $value['max'];
					break;
				default:
					$url .= '&' . $key . '=' . $value;
					break;
			}
		}

		$url = substr( $url, 1 );
		return $url;
	}

	function GetChooseUrlCurrect( $arg )
	{
		$url = "";
		unset( $arg['geturl'] );

		foreach ( $arg as $key => $value )
		{
			switch ( $key )
			{
				case 'category' :
					$url .= '&cid=' . $value;
					break;
				case 'index' :
					if ( is_array( $value ) )
						$url .= '&iid=' . implode( '-', $value );
					break;
				case 'brand' :
					$url .= '&bid=' . $value;
					break;

				case 'kw' : 
					// $value = str_replace(' ','+',$value);
					$url .= '&kw=' . urlencode( $value );
					break;
				case 'price' :
					$url .= '&minprice=' . $value['min'];
					$url .= '&maxprice=' . $value['max'];
					break;
				default:
					$url .= '&' . $key . '=' . $value;
					break;
			}
		}

		$url = substr( $url, 1 );
		return $url;
	}

	/**
	 * 格式化查询结果为一个可以用的CHOOSE数组
	 */

	function Format( $chooseBox )
	{
		foreach ( $chooseBox as $key => $value )
		{
			switch ( $key )
			{
				case 'selector':
					if ( $value )
						$choose[$key] = $value;
					break;

				case 'index':
					if ( $value )
						$choose[$key] = array( 'title' => '热门', 'content' => $value );
					break;

				case 'category':
					if ( $value )
						$choose[$key] = array( 'title' => '分类', 'content' => $value );
					break;

				case 'brand':
					if ( $value )
						$choose[$key] = array( 'title' => '品牌', 'content' => $value );
					break;

				case 'sex':
					if ( $value )
						$choose[$key] = array( 'title' => '性别', 'content' => $value );
					break;

				case 'price':
					if ( $value )
						$choose[6] = array( 'title' => '价格', 'content' => $value );
					break;

				case 'shop':
					if ( $value )
						$choose[5] = array( 'title' => '店铺', 'content' => $value );
					break;
			}
		}
		ksort( $choose );
		return $choose;
	}

	static function GetH1byget( $get )
	{ 
		// 记住get顺序
		$i = 1;
		foreach ( $get as $key => $no )
		{
			$sort[$key] = $i;
			$i += 1;
		}

		$pre_h1 = array();

		foreach ( $get as $key => $value )
		{
			switch ( $key )
			{
				case 'category':
					if ( $value )
					{	
						$pre_h1[$sort[$key]] = array(
							'content' => self::GetCategoryH1( $value, $get ),
							'is_array' => 1, 
						);
					}
					break;
				case 'kw' :
					$pre_h1[$sort[$key]] = array(
						'content' => self::GetKwH1( $value, $get ),
						'is_array' => 0,
						'is_top' => 1,
						'break' => true, 
					);
					break;
				case 'index' :
					if ( $value )
					{
						$pre_h1[$sort[$key]] = array(
							'content' => self::GetIndexH1( $value, $get ),
							'is_array' => 1, 
						);
					}

					break;
				case 'brand':
					$pre_h1[$sort[$key]] = array( 'content' => self::GetBrandH1( $value, $get ),
						'is_array' => 0, 
						);
					break;
				case 'price':
					$pre_h1[$sort[$key]] = array( 'content' => self::GetPriceH1( $value, $get ),
						'is_array' => 0, 
						);
					break;
				case 'shop':
					$pre_h1[$sort[$key]] = array( 'content' => self::GetShopH1( $value, $get ),
						'is_array' => 0, 
						);
					break;
				case 'sex':
					$pre_h1[$sort[$key]] = array( 'content' => self::GetSexH1( $value, $get ),
						'is_array' => 0, 
						);
					break;
			}
		}

		$h1 = array();

		// 整理h1
		foreach ( $pre_h1 as $key => $value )
		{
			if ( $value['is_top'] == 1 )
			{
				if ( $value['is_array'] == 1 && count( $value['content'] ) > 0 )
				{
					foreach ( $value['content'] as $one )
					{
						$h1[] = $one;
					}
				}elseif ( $value['is_array'] == 0 )
				{
					$h1[] = $value['content'];
				};
				unset( $pre_h1[$key] );
			}
		}

		foreach ( $pre_h1 as $key => $value )
		{
			if ( $value['is_array'] == 1 && count( $value['content'] ) > 0 )
			{
				foreach ( $value['content'] as $one )
				{
					$h1[] = $one;
				}
			}elseif ( $value['is_array'] == 0 )
			{
				$h1[] = $value['content'];
			};
		} 
		// 分词
		$h1temp = array();
		foreach( $h1 as $key => $value )
		{
			if ( $value['break'] )
			{
				$names = explode( ' ', $value['name'] );
				foreach( $names as $key2 => $name )
				{
					$newnames = $names;
					unset( $newnames[$key2] );
					$h1temp[] = array( 'name' => $name,
						'link' => $value['link'] . '&kw=' . urlencode( implode( ' ', $newnames ) ), 
						);
				}
				unset( $h1[$key] );
			}
		}

		foreach ( $h1 as $value )
		{
			$h1temp[] = $value;
		}

		return $h1temp;
	}

	/**
	 * 根据当前条件去掉部分得到choose的condition
	 */

	static function Removeselect( $value, $list )
	{
		foreach ( $list as $key => $id )
		{
			if ( $id != $value )
			{
				$n[$key] = $id;
			}
			else
			{
				return $n;
			}
		}
		return $n;
	}

	static function getChoosePriceData( $price_array, $get )
	{
		$price = array( 
			array( 'name' => '0-100',
				'min' => '0',
				'max' => '100', 
				),
			array( 'name' => '100-300',
				'min' => '100',
				'max' => '300', 
				),
			array( 'name' => '300-500',
				'min' => '300',
				'max' => '500', 
				),
			array( 'name' => '500-800',
				'min' => '500',
				'max' => '800', 
				),
			array( 'name' => '800-1000',
				'min' => '800',
				'max' => '1000', 
				),
			array( 'name' => '100以上',
				'min' => '1000', 
				), 
			);

		if ( is_Array( $price_array ) )
		{
		}
		return $price_array;
	}

	/**
	 * 修正CHOOSE的INDEX，COUNT数据
	 */

	static function FixIndexCount( $index_array, $i )
	{
		$objPcIdxValue = Core::ImportMl('Product_IndexValue');
		if ( is_array( $index_array ) )
		{
			foreach ( $index_array as $key => $value )
			{
				$list = $objPcIdxValue->getPcIdxValueList( $key );

				if ( $i[$key] )
				{
					$countuse = $i[$key];
				}
				else
				{
					$countuse = $value;
				}

				foreach ( $list as $k => $v )
				{
					$list[$k]['count'] = $countuse[$v['id']];
				}
				$new[$key] = $list;
			}
		}
		return $new;
	}

	/**
	 * 返回sex所需要的数组
	 */

	static function FormatSex( $sex_array, $get )
	{
		if ( is_Array( $sex_array ) )
		{
			$sexlist = self::GetSex(); 
			// 为brand添加上count和link
			foreach ( $sexlist as $k => $one )
			{
				$sexlist[$k]['count'] = $sex_array[$one['id']];
				$g = $get;
				$g['sex'] = $one['id'];
				$sexlist[$k]['link'] = self::GetChooseUrl( $g ); 
				// 添加on标记
				if ( $one['id'] == $get['sex'] )
				{
					$sexlist[$k]['on'] = 1;
				}
			}
		}

		return $sexlist;

		$re = array( 'title' => '性别',
			'content' => $sexlist 
			);
		return $re;
	}

	/**
	 * 得到所有的sex列表
	 */

	static function GetSex()
	{
		$sex = array( 
			array( 'name' => '男生',
				'id' => 1, 
				),
			array( 'name' => '女生',
				'id' => 2, 
				),
			array( 'name' => '儿童',
				'id' => 4, 
				), 
			);

		return $sex;
	}

	/**
	 * 返回price所需要的数组
	 */

	static function FormatPrice( $price_array, $get )
	{
		if ( is_array( $price_array ) )
		{
			$pricelist = self::GetPrice(); 
			// 为brand添加上count和link
			foreach ( $pricelist as $k => $one )
			{
				$pricelist[$k]['count'] = $price_array[$k];

				if ( !$pricelist[$k]['count'] )
				{
					unset( $pricelist[$k] );
					continue;
				}
				$g = $get;
				$g['price']['min'] = $one['minprice'];
				$g['price']['max'] = $one['maxprice'];
				$pricelist[$k]['link'] = self::GetChooseUrl( $g ); 
				// 添加on标记
				if ( $one['minprice'] == $get['price']['min'] && $one['maxprice'] == $get['price']['max'] )
				{
					$pricelist[$k]['on'] = 1;
				}
			}
		}

		return $pricelist;

		$re = array( 'title' => '价格',
			'content' => $pricelist 
			);
		return $re;
	}

	static function getPriceCondition()
	{
		return array( '0' => array( 0, 100 ),
			'1' => array( 100, 300 ),
			'2' => array( 300, 500 ),
			'3' => array( 500, 800 ),
			'4' => array( 800, 1000 ),
			'5' => array( 1000 ), 
			);
	}

	static function getPrice()
	{
		$price = array( 
			array( 'name' => '0－100',
				'minprice' => 0,
				'maxprice' => 100, 
				),
			array( 'name' => '100－300',
				'minprice' => 100,
				'maxprice' => 300, 
				),
			array( 'name' => '300－500',
				'minprice' => 300,
				'maxprice' => 500, 
				),
			array( 'name' => '500－800',
				'minprice' => 500,
				'maxprice' => 800, 
				),
			array( 'name' => '800－1000',
				'minprice' => 800,
				'maxprice' => 1000, 
				),
			array( 'name' => '1000以上',
				'minprice' => 1000, 
				), 
			);
		return $price;
	}

	static function FormatShop( $shop_array, $get )
	{
		$objShop = Core::ImportMl( 'Shop' );

		if ( is_array( $shop_array ) && count( $shop_array ) > 0 )
		{
			foreach ( $shop_array as $shopone => $count )
			{
				$shopcon[] = $shopone;
			}

			$shoplist = $objShop->getBaseShopList( $shopcon ); 
			// 为brand添加上count和link
			foreach ( $shoplist as $k => $one )
			{
				$shoplist[$k]['count'] = $shop_array[$one['id']];
				$g = $get;
				$g['shop'] = $one['id'];
				$shoplist[$k]['link'] = self::GetChooseUrl( $g );

				if ( !$shoplist[$k]['count'] )
				{
					unset( $shoplist[$k] );
					continue;
				} 
				// 添加on标记
				if ( $one['id'] == $get['shop'] )
				{
					$shoplist[$k]['on'] = 1;
				}
			}
		}

		return $shoplist;

		$re = array( 'title' => '店铺',
			'content' => $shoplist 
			);
		return $re;
	} 
	// 以下都是获得H1的函数
	static function GetKwH1( $value, $get )
	{
		unset( $get['kw'] );
		$h1 = array(
			'name' => $value,
			'link' => self::GetChooseUrl( $get ),
			'break' => true, 
		);

		if ( $get['from'] == 'search' )
		{
			$h1['close'] = 0;
		}
		return $h1;
	}

	static function GetIndexH1( $value, $get )
	{
		$objPcIdxValue = Core::ImportMl('Product_IndexValue');
		$names = $objPcIdxValue->getPcIdxValueBatch($value, true);
		foreach ( $value as $id )
		{
			$g = $get;
			$g['index'] = self::Removeselect( $id, $g['index'] );

			$h1[] = array(
				'name' => $names[$id]['name'],
				'link' => self::GetChooseUrl( $g ), 
			);
		}
		return $h1;
	}

	static function GetCategoryH1( $value, $get )
	{
		unset( $get['category'] );
		unset( $get['index'] );
		unset( $get['sex'] );

		$objCategoryProduct = Core::ImportMl('Category_Product');
		$objCategoryProduct->BuildTree();
		$cate = $objCategoryProduct->GetParentList( $value );
		$cate[$value] = $objCategoryProduct->GetCategory( $value, true );
		foreach ( $cate as $one )
		{
			$get['category'] = $one['pid'];
			
			$h1[] = array(
				'name' => $one['name'],
				'link' => self::GetChooseUrl( $get ), 
			);
		}

		return $h1;
	}

	static function GetBrandH1( $value, $get )
	{
		unset( $get['brand'] );
		$objBrand = Core::ImportMl('Brand');
		$brand = $objBrand->getBrandInfo( $value );
		$h1 = array( 'name' => $brand['name'],
			'link' => self::GetChooseUrl( $get ), 
			);
		return $h1;
	}

	static function GetPriceH1( $value, $get )
	{
		unset( $get['price'] );

		if ( $value['min'] && $value['max'] )
		{
			$name = $value['min'] . '-' . $value['max'];
		}elseif ( $value['min'] )
		{
			$name = $value['min'] . '以上';
		}elseif ( $value['max'] )
		{
			$name = '0-' . $value['max'];
		}

		$h1 = array( 'name' => $name,
			'link' => self::GetChooseUrl( $get ), 
			);
		return $h1;
	}

	static function GetShopH1( $value, $get )
	{
		unset( $get['shop'] );
		$objShop = Core::ImportMl( 'Shop' );
		$shop = $objShop->getShopInfo( $value );
		$h1 = array( 'name' => $shop['name'],
			'link' => self::GetChooseUrl( $get ), 
			);
		return $h1;
	}

	static function GetSexH1( $value, $get )
	{
		unset( $get['sex'] );

		if ( $value == 1 )
		{
			$name = '男生';
		}elseif ( $value == 2 )
		{
			$name = '女生';
		}elseif ( $value == 3 )
		{
			$name = '中性';
		}elseif ( $value == 4 )
		{
			$name = '儿童';
		}

		$h1 = array( 'name' => $name,
			'link' => self::GetChooseUrl( $get ), 
			);
		return $h1;
	}

	static function GetPager( $get )
	{
		$get['offset'] += $get['limit'];
		$get['pager'] = 1;
		$link = self::GetChooseUrl( $get );
		return $link;
	}

}
