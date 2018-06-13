<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\History;
use App\Entity\Question;
use App\Entity\Reponse;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;



class DefaultController extends Controller
{
    /**
     * @Route("/", name="home", methods="GET")
     */
    public function index()
    {
        $categories = $this->getDoctrine()->getRepository(Categorie::class)->findAll();
        
        return $this->render('default/index.html.twig', [
            'controller_name' => 'DefaultController',
            'categories' => $categories
        ]);
    }

    /**
     * @Route("/quiz/{category}", name="run_quiz", requirements={"category"="\d+"}, methods="GET")
     */
    public function runQuiz(Request $request, Categorie $category)
    {
        $question = $request->query->get('question') ?: 1;
        
        $question = $category->getQuestions()->get($question - 1);
        $answers = $question->getReponses()->getValues();
        shuffle($answers);

        return $this->render('default/run-quiz.html.twig', [

            'category' => $category,
            'question' => $question,
            'answers' => $answers
        ]);
    }

    /**
     * @Route("/check/{question}", requirements={"question"="\d+"})
     */
    public function checkAnswer(Request $request, SessionInterface $session, Question $question)
    {
        $reqBody = json_decode($request->getContent());
        $answerId = intval($reqBody->answerId);

        $questionId = $question->getId();
        $category = $question->getCategory();
        $categoryName = $category->getName();

        $answers = $question->getReponses()->getValues();
        
        $isCorrect = false;
        $expected = null;

        // get expected answerId
        foreach ($answers as $answer) {
            if ($answer->getReponseExpected()) {
                if ($answer->getId() === $answerId) {
                    $isCorrect = true;
                } else {
                    $expected = $answer->getReponse();
                }
                break;
            }
        }

        // deletelater
        $debug = false;

        // save history in db
        if ($reqBody->isLoggedIn) {
            $doctrine = $this->getDoctrine();
            $entityManager = $doctrine->getManager();

            $user = $doctrine->getRepository(User::class)->find($reqBody->isLoggedIn);
            $answer = $doctrine->getRepository(Reponse::class)->find($reqBody->answerId);
            
            // create or update whether record exists or not
            $history = $doctrine->getRepository(History::class)->findOneBy([
                'question' => $questionId,
                'user' => $user->getId()
            ]);
            
            if ($history) {
                $history->setAnswer($answer);
            } else {
                $history = new History();
                $history->setUser($user);
                $history->setQuestion($question);
                $history->setAnswer($answer);
                
                $entityManager->persist($history);
            }
            
            $entityManager->flush();
        }

        // get next question index
        $questions = $category->getQuestions()->getValues();
        $queryString = null;

        foreach ($questions as $key => $question) {
            if ($question->getId() === $questionId && ($key + 2) <= 9) {
                $queryString = '?question=' . ($key + 2);
                break;
            }
        }

        $response = new JsonResponse([
            'isCorrect' => $isCorrect,
            'expected' => $expected,
            'queryString' => $queryString,
        ]);
        
        return $response;
    }

    /**
     * @Route("/scoreboard/{category}", name="scoreboard", requirements={"category"="\d+"})
     */
    function scoreboard(Request $request, Categorie $category)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $doctrine = $this->getDoctrine();
        $categoryId =  $category->getId();
        $histories = $doctrine->getRepository(History::class)->findBy(['user' => $this->getUser()->getId()]);

        $filteredHistories = array_filter($histories, function($history) use ($categoryId) {
            return $history->getQuestion()->getCategory()->getId() === $categoryId;
        });

        return $this->render('default/scoreboard.html.twig', [
            'categorie' => $category,
            'histories' => $filteredHistories,
            'questionsCount' => count($category->getQuestions())
        ]);
    }
}