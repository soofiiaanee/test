<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\User;
use App\Entity\Voiture;
use App\Entity\Marque;
use App\Entity\Modele;
use App\Entity\Region;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;


class DefaultController extends AbstractController
{
    /**
     * @Route("", name="home")
     */
    public function home(Request $request)
    {
        $defaultData = ['message' => 'Type your message here'];
        $form = $this->createFormBuilder($defaultData)
        ->add('marque', EntityType::class, [
                                                'class' => Marque::class,
                                                'choice_label' => 'marque',
                                                'multiple' => false,
                                                'expanded' => false,
                                                'attr'=>['class'=>'form-control']
                                                ])
            ->add('region', EntityType::class, [
                                                'class' => Region::class,
                                                'choice_label' => 'region',
                                                'multiple' => false,
                                                'expanded' => false,
                                                'attr'=>['class'=>'form-control']
                                                ])
            ->add('rechercher', SubmitType::class)
        ->getForm();

        $form->handleRequest($request);
        $allVoitures = array();
        if ($form->isSubmitted() && $form->isValid()) 
            {
                $marque = $form['marque']->getData();
                $region = $form['region']->getData();
                
                $voitures = $region->getVoitures();
                foreach($voitures as $voiture)
                {
                    if($voiture->getModele()->getMarque() == $marque)
                    {
                        $allVoitures[] = $voiture; 
                    }
                }
                
                return $this->render('forms/resultats.html.twig', ['form' => $form->createView(),'voitures'=>$allVoitures]);
            }
        return $this->render('forms/home.html.twig', ['form' => $form->createView()]);
    }
    
    /**
     * @Route("/profil", name="profil")
     */
    public function profil(Request $request)
    {
        return $this->render('forms/profil.html.twig');
    }
    
    
    /**
     * @Route("/supprimerVoiture/{id}", name="supprimerVoiture")
     */
    public function supprimerVoiture(Request $request,$id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        
        $voiture = $this->getDoctrine()->getRepository('App\Entity\Voiture')->find($id);
        if($voiture)
        {
           $user->removeVoiture($voiture);
           $entityManager->persist($user);
           $entityManager->flush();
        }
        
        return $this->redirect($this->generateUrl('profil'));
    }
    
    
}
