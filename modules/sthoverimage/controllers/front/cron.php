<?php
class SthoverimageCronModuleFrontController extends ModuleFrontController
{
	public function initContent()
	{
		if (Tools::getValue('token') != md5('sthoverimage')) {
			die('Token error');
		}
		StHoverImage::buildingHover(10000);
        die('Okay');
	}
}
