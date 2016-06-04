<?php

namespace Ecommerce\EcommerceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Ecommerce\EcommerceBundle\Entity\Categories;
use Ecommerce\EcommerceBundle\Controller\PanierController;
use Ecommerce\EcommerceBundle\Form\RechercheType;
use Ecommerce\EcommerceBundle\Twig\Extension;


class ProduitsController extends Controller
{
    public function produitsAction(Categories $categorie = null)
    {
        //var_dump($categorie);
       // die();   
        $session          = $this->getRequest()->getSession();
        // création de l'entity manager
        $em               = $this->getDoctrine()->getManager();
        if($categorie    != null)
        $produits         = $em->getRepository('EcommerceBundle:Produits')->byCategorie($categorie);   
        else
        // création du repository 
        $produits         = $em->getRepository('EcommerceBundle:Produits')->findby(array());
        if($session->has('panier'))
        $panier           = $session->get('panier');
        else 
            $panier       = false;
        return $this->render('EcommerceBundle:Default:produits/layout/produits.html.twig', array('produits'    => $produits,
                                                                                                 'panier'      => $panier ));
    }
    
    
    public function presentationAction($id)
    {
        $session         = $this->getRequest()->getSession();
        // créer l'entitie manager
        $em              = $this->getDoctrine()->getManager();
        $produit         = $em->getRepository('EcommerceBundle:Produits')->find($id);
        if(!$produit){ throw $this->createNotFoundException('La page n\'existe pas ');}
        
        if($session->has('panier'))
        {
            $panier      = $session->get('panier');
        }else {
            $panier      = false;
        }
        return $this->render('EcommerceBundle:Default:produits/layout/presentation.html.twig', array('produit' => $produit,
                                                                                                      'panier' => $panier ));
    }
    
    /*
    public function categorieAction($categorie)
    {
        // query bulder en fonction de la catégorie afficher les produits
        //créer le entitie manager
        $em = $this->getDoctrine()->getManager();
        // on va dans le repository
        $produits = $em->getRepository('EcommerceBundle:Produits')->byCategorie($categorie);
        $categorie = $em->getRepository('EcommerceBundle:Categories')->find($categorie);
        //condition
        if(!$categorie){ throw $this->createNotFoundException('la page n\'existe pas '); }
        return $this->render('EcommerceBundle:Default:produits/layout/categorieProduits.html.twig', array('produits' => $produits));
    }
    
    */
    
    public function rechercheAction()
    {
        $form      = $this->createForm( new RechercheType()); 
        return $this->render('EcommerceBundle:Default:recherche/modulesUsed/recherche.html.twig', array('form' => $form->createView() ));   
    }
        
    public function rechercheTraitementAction()
    {
     $form         = $this->createForm( new RechercheType());    
        if( $this->get('request')->getMethod() == 'POST')
        {
            $form->bind($this->get('request'));
            echo $form['recherche']->getData();
        //die();
       $em = $this->getDoctrine()->getManager();
       $produits   = $em->getRepository('EcommerceBundle:Produits')->recherche($form['recherche']->getData());
        }else{
            throw $this->createNotFoundException('La page n\'exist' );
        }
       return $this->render('EcommerceBundle:Default:produits/layout/produits.html.twig', array('produits' => $produits));
       
    }
    
    
}
