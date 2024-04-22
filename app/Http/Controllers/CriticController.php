<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Critic;
use App\Repository\CriticRepositoryInterface;

class CriticController extends Controller
{
    private CriticRepositoryInterface $CriticRepository;

    public function __construct(CriticRepositoryInterface $criticRepository) 
    {
        $this->criticRepository = $criticRepository;
    }
}
