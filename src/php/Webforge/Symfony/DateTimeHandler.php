<?php

namespace Webforge\Symfony;

use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\Context;
use Webforge\Common\DateTime\DateTime;

class DateTimeHandler implements SubscribingHandlerInterface {

  // this is like DateTime::ISO8601 but with fractional seconds
  const ISO_FORMAT = 'Y-m-d\TH:i:s.uO';

  public static function getSubscribingMethods() {
    return array(
      array(
        'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
        'format' => 'json',
        'type' => 'WebforgeDateTime',
        'method' => 'serializeDateTimeToJson',
      ),

      array(
        'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
        'format' => 'json',
        'type' => 'WebforgeDateTime',
        'method' => 'deserializeDateTimeFromJson',
      ),
    );
  }

  public function serializeDateTimeToJson(JsonSerializationVisitor $visitor, DateTime $dateTime, array $type, Context $context) {
    return self::export($dateTime);
  }

  public static function export(DateTime $dateTime) {
    return $dateTime->format(self::ISO_FORMAT);
  }

  public static function parse($jsonDate) {
    return DateTime::parse(self::ISO_FORMAT, $jsonDate);
  }

  public function deserializeDateTimeFromJson(JsonDeserializationVisitor $visitor, $json, array $type, Context $context) {
    return self::parse($json->date);
  }

  public function webforgeDateTimeBetween($startDate = '-30 years', $endDate = 'now') {
    return new DateTime(\Faker\Provider\DateTime::dateTimeBetween($startDate, $endDate));
  }

  public function webforgeDateTime($string) {
    return new \Webforge\Common\DateTime\DateTime($string);
  }
}
