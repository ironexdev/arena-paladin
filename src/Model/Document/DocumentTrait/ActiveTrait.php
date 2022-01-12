<?php declare(strict_types=1);

namespace Paladin\Model\Document\DocumentTrait;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Exception;

/* @ODM\HasLifecycleCallbacks */
trait ActiveTrait
{
    /** @ODM\Field(type="boolean") */
    private bool $active = false;

    /**
     * @return bool
     */
    public function getActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }
}
