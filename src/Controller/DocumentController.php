<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Date ;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Document ;
use App\Entity\PropertySearch ;
use App\Form\PropertySearchType ;
use Knp\Component\Pager\PaginatorInterface;
use App\Form\DocumentType ;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;



class DocumentController extends AbstractController
{
     /**
      * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_AGENT')")
     * @Route("/ajouterdocument", name="ajouterdocument")
     */
    public function creationdocument(): Response
    {
      
        $entityManager = $this->getDoctrine()->getManager();

        $Document = new Document();
        $form= $this->createForm(DocumentType::class,$Document);
        $Document->setType('test');
        $Document->setSource('test');
        $Document->setObjet('test');
        $Document->setNumInterne('1111');
        $date = new \DateTime('2019-06-05  12:15:30');
        $Document->setDateDocumentation($date);

       
        $entityManager->persist($Document);  // notifier Doctrine qu'il ya un objet à enregister
        $entityManager->flush();
        return new Response('Document enregisté avec id   '.$Document->getId());
        return $this->render('Document/ajouter.html.twig',['form'=>$form->createView()]);
       
    }
     /**
      * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_AGENT')") 
     * @Route("/createdocument", name="createdocument")
     */

    public function ajouterdocument(Request $request):Response
    {
             $Document = new Document() ; 
             $form = $this->createForm(DocumentType::class, $Document);
             $form->handleRequest($request);
             if($form->isSubmitted()){
                 $entityManager=$this->getDoctrine()->getManager();
                 $entityManager->persist($Document);
                 $entityManager->flush($Document);
    
             return $this->redirectToRoute('afficherdocument'); }
        return $this->render('document/ajouter.html.twig',
        ['form'=> $form->createView() ]) ; }

  /**
    * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_AGENT')")
     * @Route("/afficherdocument", name="afficherdocument")
     */
    public function afficherdocument(Request $request,PaginatorInterface $paginator):Response 
    {


        $Document = $this->getDoctrine()->getRepository(Document::class)->findAll ();

        $Document = $paginator->paginate(
          $Document, // Requête contenant les données à paginer (ici nos articles)
          $request->query->getInt('page', 2), // Numéro de la page en cours, passé dans l'URL, 1 si aucune page
          6 // Nombre de résultats par page
      );
      
      









        return $this->render('Document/index.html.twig', [
                  'Document' => $Document,]);

                return $this->render('Document/show.html.twig',['Document' => $Document] );

    }
    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("modifierdocument/{id}", name="modifierdocument")
     * Method({"GET", "POST"})
     */


    public function edit(Request $request, $id) {
        $Document = new Document();
        $Document = $this->getDoctrine()->getRepository(Document::class)->find($id);

        $form = $this->createForm(DocumentType::class,$Document);

        $form->handleRequest($request);
        if($form->isSubmitted()) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            return $this->redirectToRoute('afficherdocument');
        }

        return $this->render('Document/index.html.twig', [
            'Document' => $Document,]);   }


      /**
       * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_AGENT')")
     * @Route("/document/{id}", name="document")
     */
    public function show($id) {
        $Document = $this->getDoctrine()->getRepository(Document::class)->find($id);
  
        return $this->render('Document/show.html.twig', [
            'Document' => $Document,]);
      }
    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/deletedocument/{id}",name="delete_document")
     * @Method({"DELETE"})
     */
    public function delete(Request $request, $id) {
        $Document= $this->getDoctrine()->getRepository(Document::class)->find($id);
  
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($Document);
        $entityManager->flush();
  
        $response = new Response();
        $response->send();

        return $this->redirectToRoute('afficherdocument');
    }
     /**
      * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_AGENT')")
     * @Route("/modifierdocument/{id}", name="modifierdocument")
     * Method({"GET", "POST"})
     */
    public function modifier(Request $request, $id) {
        $Document = new Document ();
        $Document = $this->getDoctrine()->getRepository(Document::class)->find($id);
  
        $form = $this->createFormBuilder($Document)
          ->add('Type', TextType::class)
          ->add('Source', TextType::class)
          ->add('Objet', TextType::class)
          ->add('NumInterne', TextType::class)

          ->add('save', SubmitType::class, array(
            'label' => 'Modifier'  ,
              ) )     
          
              ->add('saveAdd', SubmitType::class, array(
                'label' => 'Annuler'  ))
          
        ->getForm();
  
        $form->handleRequest($request);
        if($form->isSubmitted() ) {
  
          $entityManager = $this->getDoctrine()->getManager();
          $entityManager->flush();
  
          return $this->redirectToRoute('afficherdocument');
        }
  
        return $this->render('document/edit.html.twig', ['form' => $form->createView()]);
      }
    

  /**
    *@Route("/",name="article_list")
    */
    public function home(Request $request , PaginatorInterface $paginator)
    {

      
      $propertySearch = new PropertySearch();
      $form = $this->createForm(PropertySearchType::class,$propertySearch);
      $form->handleRequest($request);
     //initialement le tableau des articles est vide, 
     //c.a.d on affiche les articles que lorsque l'utilisateur clique sur le bouton rechercher
      $Document= [];
      
     if($form->isSubmitted() && $form->isValid()) {
     //on récupère le nom d'article tapé dans le formulaire
      $nom = $propertySearch->getNom();   
      if ($nom!="") 
        //si on a fourni un nom d'article on affiche tous les articles ayant ce nom
        $Document= $this->getDoctrine()->getRepository(Document::class)->findBy(['nom' => $nom] );

        
      else   
        //si si aucun nom n'est fourni on affiche tous les articles
        
        $Document= $this->getDoctrine()->getRepository(Document::class)->findAll();
       



     }
      return  $this->render('document/recherche.html.twig',[ 'form' =>$form->createView(), 'Document' => $Document]);  
    }











     
}
