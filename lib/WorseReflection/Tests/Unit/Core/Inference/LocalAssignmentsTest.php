<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Inference;

use Phpactor\WorseReflection\Core\Inference\Assignments;
use Phpactor\WorseReflection\Core\Inference\LocalAssignments;

class LocalAssignmentsTest extends AssignmentstTestCase
{
    protected function assignments(): Assignments
    {
        return LocalAssignments::create();
    }
}
