<?php

use App\Models\Questions;
use App\Services\QuizService;

it('return 20 question ' , function (){
  $service = new QuizService();
  $quiz = $service->detectedQuiz(Questions::factory(20)->create()  ,"short" , 20 , "hard"  , null);

})