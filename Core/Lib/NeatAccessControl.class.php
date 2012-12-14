<?php

class NeatAccessControl
{
	var $allow = array();
	var $accessModule = array();
	var $globalTest = false;

	var $testModule = false;
	var $testAction = false;

	function NeatAccessControl( $allow = false, $accessModule = array() )
	{
		$this->SetAllow( $allow );
		$this->SetModule( $accessModule );
	}

	function SetAllow( $allow )
	{
		if ( is_string( $allow ) )
			$this->allow = GetExplode( ',', $allow );
		elseif ( is_array( $allow ) )
			$this->allow = $allow;
	}

	function SetModule( $accessModule )
	{
		if ( is_array( $accessModule ) )
			$this->accessModule = $accessModule;
	}

	function GlobalAllow()
	{
		return false;
	}

	function GlobalModule()
	{
		return false;
	}

	function ToTest( $module, $action = false )
	{
		$this->testModule = $module;
		$this->testAction = $action;

		if ( $this->GlobalAllow() )
			return true;

		if ( $this->GlobalModule() )
			return true;

		if ( is_array( $module ) )
			$module = implode( '.', $module );

		if ( !$this->InModule( $module, $action ) )
			return true;

		if ( $action )
			$module .= ".{$action}";

		$hash = md5( $module );
		$key = substr( $hash, 0, 2 ) . substr( $hash, 8, 2 ) . substr( $hash, 16, 2 ) . substr( $hash, 30, 2 );

		if ( !is_array( $this->allow ) )
			return false;

		if ( in_array( $key, $this->allow ) )
			return true;
		else
			return false;
	}

	function InModule( $module, $action = false )
	{
		if ( $action )
			$module .= ".{$action}";
		
		if ( $this->globalTest )
			return true;
		
		if ( !is_array( $this->accessModule ) )
			return false;

		if ( is_string( $module ) )
			$module = explode( '.', $module );

		if ( !is_array( $module ) )
			return false;

		$moduleDepth = count( $module ) - 1;

		if ( $moduleDepth < 0 )
			return false;

		$accessList = $this->accessModule;

		$i = 0;
		while ( $accessList && $i <= $moduleDepth )
		{
			if ( $accessList[$module[$i]] )
			{
				if ( is_array( $accessList[$module[$i]]['sub'] ) && $i < $moduleDepth )
				{
					$accessList = $accessList[$module[$i]]['sub'];
				}
				else
				{
					if ( $i < $moduleDepth )
						return false;
					else
						return true;
				}
			}
			else
			{
				return false;
			}

			$i++;
		}

		return false;
	}
}
