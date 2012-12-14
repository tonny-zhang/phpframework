<?php
class Helper_Form_Validate
{
	private $_errorMessage = array();

	private $_rules = array();

	private $_elements; 
	// 是否完全检查
	protected $complete_check = true;

	function __construct( $data )
	{
		$this -> _elements = $this -> trimdata( $data );
	}

	/**
	 * 遇到第一个错误便返回
	 */
	public function first_error_only()
	{
		$this -> complete_check = false;
	}

	/**
	 * 执行所有验证规则,收集所有错误
	 */
	public function show_all_errors()
	{
		$this -> complete_check = true;
	}

	/**
	 * 去掉数据两边的空格
	 * 
	 * @fields array/string
	 * @return array /string
	 */
	private function trimdata( $fields )
	{
		if ( is_array( $fields ) )
		{
			foreach ( $fields as $key => $value )
			{
				$fields[$key] = $this -> trimdata( $value );
			}
			return $fields;
		}
		else
		{
			return trim( $fields );
		}
	}

	/**
	 * 添加验证条件
	 * 
	 *   $key       => 表单字段名
	 *   $message   => 错误时返回消息
	 *   $method    => 验证方法
	 *   $parameter => 附加参数
	 * 
	 *   $form->addrule('username','用户名不能为空','not_empty');
	 */
	function addrule( $key, $message, $method, $parameter = null )
	{
		$rule = array( 'key' => $key,
			'message' => $message,
			'method' => $method,
			'parameter' => $parameter, 
			);
		$this -> _rules[] = $rule;
	}

	/**
	 * 验证函数
	 * 
	 * @return true /false
	 */

	function is_valid()
	{
		return $this -> _validate( $this -> _elements );
	}

	private function _validate( $data )
	{
		if ( !is_array( $data ) )
		{
			throw new FDX_Exception( '非法输入' );
		}

		$rules = $this -> _rules;
		$is_valid = true;

		foreach ( $rules as $rule )
		{
			$name = $rule['key'];
			$method = $rule['method'];
			$parameter = $rule['parameter'];
			$message = $rule['message'];

			$vdata = '';
			if ( is_array( $name ) )
			{
				$vdata = array();
				foreach ( $name as $nameone )
				{
					$vdata[] = $data[$nameone];
				}
			}
			else
			{
				$vdata = $data[$name];
			}

			$check = $this -> $method( $vdata, $parameter );

			if ( !$check )
			{
				$is_valid = false;
				if ( is_array( $name ) )
				{
					$errorname = current( $name );
				}
				else
				{
					$errorname = $name;
				}
				$this -> _errorMessage[$errorname][] = $message; 
				// 如果设置不进行完全检查,则发现第一个错误后跳出最外层循环
				if ( !$this -> complete_check )
				{
					return $is_valid;
				}
			}
		}
		return $is_valid;
	}

	/**
	 * 返回错误信息
	 * 
	 * @return array 
	 */
	function errorMessage()
	{
		return $this -> _errorMessage;
	}

	/**
	 * 验证不为空
	 */

	private function not_empty( $value, $parameter )
	{
		if ( IS_ARRAY( $value ) )
		{
			$check = false;
			foreach ( $value as $one )
			{
				if ( trim( $one ) != "" )
				{
					$check = true;
				}
			}
			return $check;
		}
		else
		{
			if ( $value == "" )
			{
				return false;
			}
			else
			{
				return true;
			}
		}
	}

	/**
	 * 验证是邮件
	 */
	private function is_email( $value, $parameter )
	{
		if ( !preg_match( "/^[a-z0-9]+([._\-]*[a-z0-9])*@([-a-z0-9]*[a-z0-9]+.){2,63}[a-z0-9]+$/i", $value ) )
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * 验证最低长度
	 */
	private function min_length( $value, $parameter )
	{
		$length = ( strlen( $value ) + mb_strlen( $value, 'UTF8' ) ) / 2; //计算占位符
		if ( $length >= $parameter )
		{
			return true;
		}
		return false;
	}

	/**
	 * 验证最大长度
	 */
	private function max_length( $value, $parameter )
	{
		$length = ( strlen( $value ) + mb_strlen( $value, 'UTF8' ) ) / 2; //计算占位符
		if ( $length <= $parameter )
		{
			return true;
		}
		return false;
	}

	/**
	 * 验证相等
	 */
	private function equal( $value, $parameter )
	{
		if ( $value['0'] == $value['1'] )
		{
			return true;
		}
		return false;
	}

	/**
	 * 是否唯一
	 * 
	 * @return boolean 
	 */
	private function only( $value, $parameter )
	{
		$table = $parameter['0'];
		$field = $parameter['1'];
		$db = FDX_Model :: getInstance() -> getDb();
		$value = $db -> escapeString( trim( $value ) );
		if ( $table != "" && $field != "" )
		{
			$sql = "SELECT COUNT(*) AS `count` FROM {$table} WHERE {$field}='{$value}'";
			$count = $db -> getOne( $sql );
			if ( $count )
			{
				return false;
			}
			return true;
		}
		return false;
	}

	/**
	 * 是否是字母、数字加下划线
	 * 
	 * @param mixed $value 
	 * @return boolean 
	 */
	private function is_alnumu( $value, $parameter )
	{
		return preg_match( '/[^a-zA-Z0-9_]/', $value ) == 0;
	}

	private function badword( $value, $parameter )
	{
		$objUser = Core::ImportMl('User');
		$value = str_replace( " ", "", $value );
		$check = $objUser->checkName( $value );
		if ( !$check )
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * 验证是邮件
	 */
	private function check_captcha( $value, $parameter )
	{
		if ( md5( strtolower( $value ) ) == $_SESSION['fan_captcha'] )
		{
			return true;
		}
		return false;
	}
}
