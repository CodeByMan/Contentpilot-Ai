<?php

namespace App\Enums;

enum AiFeature: string
{
    case Content = 'content';
    case Image = 'image';
    case Audio = 'audio';
    case Chat = 'chat';
}
