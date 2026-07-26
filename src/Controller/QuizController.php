<?php

namespace App\Controller;

use App\Repository\QuizQuestionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(name: 'app_quiz_')]
class QuizController extends AbstractController
{
    #[Route('/quiz', name: 'index')]
    public function index(QuizQuestionRepository $quizQuestionRepository): Response
    {
        return $this->render('quiz/index.html.twig', [
            'totalQuestions' => $quizQuestionRepository->countAll(),
        ]);
    }

    #[Route('/quiz/jouer', name: 'play')]
    public function play(QuizQuestionRepository $quizQuestionRepository): Response
    {
        $questions = $quizQuestionRepository->findRandomSet(20);

        if ([] === $questions) {
            $this->addFlash('error', "Aucune question n'est disponible pour le moment, revenez bientôt !");

            return $this->redirectToRoute('app_quiz_index');
        }

        $questionsData = array_map(static fn ($q) => [
            'category' => $q->getCategory(),
            'question' => $q->getQuestion(),
            'options' => $q->getOptions(),
            'correctOption' => $q->getCorrectOption(),
        ], $questions);

        return $this->render('quiz/play.html.twig', [
            'questionsData' => $questionsData,
            'total' => count($questions),
        ]);
    }
}
