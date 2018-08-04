<?php

namespace Webforge\Testing;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestListenerDefaultImplementation;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\BaseTestRunner;
use PHPUnit\Util\Blacklist;

class MockeryTestListener  implements TestListener
{

    use TestListenerDefaultImplementation {
        startTestSuite as baseStartTestSuite;
        endTest as baseEndTest;
    }

    /**
     * endTest is called after each test and checks if \Mockery::close() has
     * been called, and will let the test fail if it hasn't.
     *
     * @param Test $test
     * @param float $time
     */
    public function endTest(Test $test, float $time): void
    {
        if (!$test instanceof TestCase) {
            // We need the getTestResultObject and getStatus methods which are
            // not part of the interface.
            return;
        }

        if ($test->getStatus() !== BaseTestRunner::STATUS_PASSED) {
            // If the test didn't pass there is no guarantee that
            // verifyMockObjects and assertPostConditions have been called.
            // And even if it did, the point here is to prevent false
            // negatives, not to make failing tests fail for more reasons.
            return;
        }

        try {
            // The self() call is used as a sentinel. Anything that throws if
            // the container is closed already will do.
            \Mockery::self();
        } catch (\LogicException $_) {
            return;
        }

        $e = new ExpectationFailedException(
            sprintf(
                "Mockery's expectations have not been verified. Make sure that \Mockery::close() is called at the end of the test. Consider using %s\MockeryPHPUnitIntegration or extending %s\MockeryTestCase.",
                __NAMESPACE__,
                __NAMESPACE__
            )
        );
        $result = $test->getTestResultObject();
        $result->addFailure($test, $e, $time);
    }

    public function startTestSuite(TestSuite $suite): void
    {
        Blacklist::$blacklistedClassNames[\Mockery::class] = 1;

        $this->baseStartTestSuite($suite);
    }
}