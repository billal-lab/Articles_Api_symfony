<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
/**
 * @Route("/api", name="api")
 */
class ApiController extends AbstractController
{


    private $articleRepo;
    private $em;


    public function __construct(ArticleRepository $articleRepo, EntityManagerInterface $em)
    {
        $this->articleRepo= $articleRepo;
        $this->em=$em;
    }


    /**
     * @Route("/articles", name="articles_show", methods={"GET"})
     */
    public function show(): Response
    {
        $articles = $this->articleRepo->findAll();
        return ($this->json($articles,200));
        
    }



    /**
     * @Route("/articles/{id}", name="articles_id_show", methods={"GET"})
     */
    public function showId(int $id): Response
    {       
            try {
                $article = $this->articleRepo->find($id);
                return ($this->json($article,200));
            } catch (\Throwable $th) {
                return ($this->json(["status"=>400,"message"=>"error 404"],400));
            }
    }
    


    /**
     * @Route("/articles/{id}", name="articles_id_edit", methods={"PUT"})
     */
    public function edit(Article $article,Request $request, SerializerInterface $serializer, ValidatorInterface $validator ): Response
    {
        $articleJson = $request->getContent();
        try {
            $articleSerialized = $serializer->deserialize($articleJson, Article::class,'json');

            $errors = $validator->validate($article);

            if(count($errors)>0){
                return $this->json($errors,400);
            }
            $article->setName($articleSerialized->getName());
            $article->setDescription($articleSerialized->getDescription());
            $this->em->persist($article);
            $this->em->flush();
            return $this->json([
                'status' => 200,
                'message' => 'success'
            ]);
        } catch (NotEncodableValueException $e) {
            return $this->json([
                'status' => 400,
                'message'=> 'Not encodable value exception' 
            ],400);
        }
    }



    /**
     * @Route("/articles", name="articles_create", methods={"POST"})
     */
    public function create(Request $request, SerializerInterface $serializer, ValidatorInterface $validator ): Response
    {
        $articleJson = $request->getContent();
        try {
            $article = $serializer->deserialize($articleJson, Article::class,'json');

            $errors = $validator->validate($article);

            if(count($errors)>0){
                return $this->json($errors,400);
            }
            $this->em->persist($article);
            $this->em->flush();
            return $this->json([
                'status' => 200,
                'message' => 'sucess'
            ]);
        } catch (NotEncodableValueException $e) {
            return $this->json([
                'status' => 400,
                'message'=> 'Not encodable value exception' 
            ],400);
        }
    }



    /**
    * @Route("/articles/{id}", name="articles_id_delete", methods={"DELETE"})
    */
    public function removeArticle(Article $article)
    {
        $this->em->remove($article);
        $this->em->flush();
        return $this->json([
            'status' => 200,
            'message' => 'sucess'
        ]);
    }

    
}
