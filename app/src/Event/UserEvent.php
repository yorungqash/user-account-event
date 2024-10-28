<?php

namespace App\Event;

use Symfony\Component\Validator\Constraints as Assert;

final class UserEvent
{
    #[Assert\Sequentially([
        new Assert\NotBlank(),
        new Assert\Type(type: 'int'),
        new Assert\Positive()
    ])]
    public $userId;

    #[Assert\Sequentially([
        new Assert\NotBlank(),
        new Assert\Type(type: 'string')
    ])]
    public $content;
}
