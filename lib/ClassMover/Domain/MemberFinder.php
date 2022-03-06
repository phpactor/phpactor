<?php

namespace Phpactor\ClassMover\Domain;

use Phpactor\ClassMover\Domain\Model\ClassMemberQuery;
use Phpactor\ClassMover\Domain\Reference\MemberReferences;

interface MemberFinder
{
    public function findMembers(SourceCode $source, ClassMemberQuery $memberMember): MemberReferences;
}
