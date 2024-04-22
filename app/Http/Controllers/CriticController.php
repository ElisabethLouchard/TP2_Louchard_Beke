<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Critic;
use App\Repository\CriticRepositoryInterface;
use Exception;

class CriticController extends Controller
{
    private CriticRepositoryInterface $criticRepository;

    public function __construct(CriticRepositoryInterface $criticRepository) 
    {
        $this->criticRepository = $criticRepository;
    }

    public function create(Request $request)
    {
        try
        {
            $critic = Critic::create($request->all());
            return $this->criticRepository->create($critic); 
        }
        catch(Exception $ex)
        {
            abort(SERVER_ERROR, 'Server error');
        }       
    }
}
