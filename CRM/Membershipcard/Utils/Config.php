<?php

class CRM_Membershipcard_Utils_Config
{
  /**
   * Get an item from an array using "dot" notation.
   *
   * @param  string  $key
   * @param  mixed   $default
   * @return mixed
   */
  public static function get($key, $default = null)
  {
    $session = CRM_Core_Session::singleton();
    // @todo remove comment below
    //if (!$session->get('CRM_Membershipcard_Utils_Config_Settings')) {
      $session->set('CRM_Membershipcard_Utils_Config_Settings', include(__DIR__ . '/../../../settings.php'));
    //}
    $settings = $session->get('CRM_Membershipcard_Utils_Config_Settings');

    if (!static::accessible($settings)) {
      return $default;
    }
    if (is_null($key)) {
      return $settings;
    }
    if (self::exists($settings, $key)) {
      return $settings[$key];
    }
    if (strpos($key, '.') === false) {
      return $settings[$key] ?? $default;
    }
    foreach (explode('.', $key) as $segment) {
      if (static::accessible($settings) && self::exists($settings, $segment)) {
        $settings = $settings[$segment];
      } else {
        return $default;
      }
    }
    return $settings;

  }

   /**
   * Determine whether the given value is array accessible.
   *
   * @param  mixed  $value
   * @return bool
   */
  protected static function accessible($value)
  {
      return is_array($value) || $value instanceof ArrayAccess;
  }
  /**
   * Determine if the given key exists in the provided array.
   *
   * @param  array  $settings
   * @param  string|int  $key
   * @return bool
   */
  protected static function exists($settings, $key)
  {
      if ($settings instanceof ArrayAccess) {
          return $settings->offsetExists($key);
      }
      return array_key_exists($key, $settings);
  }
}
