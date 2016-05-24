<?php

namespace Webforge\Testing;

trait TestTrait {

  /**
   * So wie assertEquals jedoch werden die arrays canonicalized (normalisiert, bzw sortiert)
   */
  public function assertArrayEquals($expected, $actual, $message = '', $maxDepth = 10) {
    return $this->assertEquals($expected, $actual, $message, 0, $maxDepth, TRUE);
  }

  public function assertThatObject($object) {
    return new ObjectAsserter($object, $this);
  }
}
