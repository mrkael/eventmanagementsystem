<?php

namespace App\Enums;

enum EventLifecycleStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Published = 'published';
}
