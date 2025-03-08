<?php

namespace Civi\Api4;

use Civi\Api4\Action\Membershipcard\SendCard;
use Civi\Api4\Generic\AbstractEntity;
use Civi\Api4\Generic\BasicGetFieldsAction;

final class Membershipcard extends AbstractEntity
{
  public static function sendCard($checkPermissions = true): SendCard
  {
    return (new SendCard(__CLASS__, __FUNCTION__))->setCheckPermissions($checkPermissions);
  }

  public static function getFields($checkPermissions = true)
  {
    return (new BasicGetFieldsAction(__CLASS__, __FUNCTION__, function ($getFieldsAction) {
      return [];
    }))->setCheckPermissions($checkPermissions);
  }
}
