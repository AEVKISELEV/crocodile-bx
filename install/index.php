<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

class hack_crocodile extends CModule
{
	public $MODULE_ID = 'hack.crocodile';
	public $MODULE_VERSION;
	public $MODULE_VERSION_DATE;
	public $MODULE_NAME;
	public $MODULE_DESCRIPTION;

	public function __construct()
	{
		$this->MODULE_VERSION = '0.0.1';
		$this->MODULE_VERSION_DATE = '2022-04-12 21:00:00';

		$this->MODULE_NAME = Loc::getMessage('CROCODILE_MODULE_INSTALL_NAME');
		$this->MODULE_DESCRIPTION = Loc::getMessage('CROCODILE_MODULE_INSTALL_DESCRIPTION');
	}

	public function InstallDB($install_wizard = true)
	{
		global $DB, $DBType, $APPLICATION;

		/*
		$errors = null;
		if (!$DB->Query("SELECT 'x' FROM up_teremok", true))
		{
			$errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . $this->MODULE_ID . '/install/db/' . $DBType . '/install.sql');
		}

		if (!empty($errors))
		{
			$APPLICATION->ThrowException(implode('', $errors));
			return false;
		}*/

		ModuleManager::registerModule($this->MODULE_ID);

		return true;
	}

	public function UnInstallDB($arParams = [])
	{
		global $DB, $DBType, $APPLICATION;

		$errors = null;
		if(array_key_exists('savedata', $arParams) && $arParams['savedata'] !== 'Y')
		{
			$errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . $this->MODULE_ID . '/install/db/' . $DBType . '/uninstall.sql');

			if (!empty($errors))
			{
				$APPLICATION->ThrowException(implode('', $errors));
				return false;
			}
		}

		return true;
	}

	public function InstallEvents()
	{
		return true;
	}

	public function UnInstallEvents()
	{
		return true;
	}

	public function InstallFiles()
	{
		CopyDirFiles(
			$_SERVER['DOCUMENT_ROOT'].'/local/modules/'.$this->MODULE_ID.'/install/public',
			$_SERVER['DOCUMENT_ROOT'].'/',
			true,
			true
		);

		CopyDirFiles(
			$_SERVER['DOCUMENT_ROOT'].'/local/modules/'.$this->MODULE_ID.'/install/js',
			$_SERVER['DOCUMENT_ROOT'].'/local/js',
			true,
			true
		);

		CopyDirFiles(
			$_SERVER['DOCUMENT_ROOT'].'/local/modules/'.$this->MODULE_ID.'/install/components',
			$_SERVER['DOCUMENT_ROOT'].'/local/components',
			true,
			true
		);

		return true;
	}

	public function UnInstallFiles()
	{
		return true;
	}

	public function DoInstall()
	{
		global $APPLICATION, $step;

		$this->errors = null;

		$this->InstallFiles();
		$this->InstallDB(false);

		$GLOBALS['errors'] = $this->errors;
		$APPLICATION->IncludeAdminFile(
			Loc::getMessage('CROCODILE_MODULE_INSTALL_TITLE'),
			$_SERVER['DOCUMENT_ROOT'].'/local/modules/'.$this->MODULE_ID.'/install/step1.php'
		);
	}

	public function DoUninstall()
	{
		global $APPLICATION, $step;

		$this->errors = [];

		ModuleManager::unRegisterModule($this->MODULE_ID);

		$step = (int)$step;
		if($step < 2)
		{
			$GLOBALS['up_module_installer_errors'] = $this->errors;

			$APPLICATION->IncludeAdminFile(
				Loc::getMessage('CROCODILE_MODULE_UNINSTALL_TITLE'),
				$_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . $this->MODULE_ID . '/install/unstep1.php'
			);
		}
		elseif($step === 2)
		{
			$this->UnInstallDB([
								   'savedata' => $_REQUEST['savedata']
							   ]);

			$this->UnInstallFiles();

			$GLOBALS['up_module_installer_errors'] = $this->errors;

			$APPLICATION->IncludeAdminFile(
				Loc::getMessage('CROCODILE_MODULE_UNINSTALL_TITLE'),
				$_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . $this->MODULE_ID . '/install/unstep2.php'
			);
		}
	}
}
