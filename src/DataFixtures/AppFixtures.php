<?php

namespace App\DataFixtures;

use App\Entity\Ad;
use Faker\Factory;
use App\Entity\User;
use App\Entity\Image;
use App\Entity\Booking;
use App\Entity\Comment;
use Cocur\Slugify\Slugify;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {

        $faker = Factory::create('fr_FR'); // on instantcie notre objet ( pas dans la boucle car besoin que d'une instanciation)
        $slugify = new Slugify(); // on instancie notre objet 

        //création d'un admin

        $admin = New User();
        $admin->setFirstname('Alexandre')
              ->setLastName('Sacré')
              ->setEmail('sacrea@epse.be')
              ->setPassword($this->passwordHasher->hashPassword($admin, 'password'))
              ->setDescription('<p>'.join('</p><p>',$faker->paragraphs(3)).'</p>')
              ->setIntroduction($faker->sentence())
              ->setRoles(['ROLE_ADMIN'])
              ->setPicture('');
        
        $manager->persist($admin);

        //gestion des utilisateurs 
        $users = []; //init d'un tableau pour récup des users pour les annonces
        $genres = ['male','femelle']; //permet de genrer pour avoir des noms d'hommes ou de femmes

        for($u=1;$u<=10;$u++)
        {
            $user = new User();
            $genre = $faker->randomElement($genres);
            $picture = 'https://picsum.photos/seed/picsum/500/500';

            $hash = $this->passwordHasher->hashPassword($user, 'password');

            $user->setFirstName($faker->firstName($genre))
                 ->setLastName($faker->lastName())
                 ->setEmail($faker->email())
                 ->setIntroduction($faker->sentence())
                 ->setDescription('<p>'.join('</p><p>',$faker->paragraphs(3)).'</p>')
                 ->setPassword($hash)
                 ->setPicture('');

            $manager->persist($user);
            $users[] = $user; // ajouter un user au tableau pour les annonces

        }

        for($i=1; $i<=30;$i++)
        {
            $ad = new Ad();
            $title = $faker->sentence(); // sentence est une méthode de Faker
            //$slug = $slugify->slugify($title); // viendra retirer les espaces,accent,etc pour rendre plus "propre"
            $coverImage = 'https://picsum.photos/seed/picsum/1000/350';
            $introduction = $faker->paragraph(2); // 2 pour le nombre de phrases dans le paragraphes ( par défault c'est 3)
            $content = '<p>'.join('</p><p>',$faker->paragraphs(5)).'</p>';//fonction php pour joindre des éléments à un tableau , le premier paramètre est le séparateur le 2ème le tableau
            // $tableau = ['Kim','Alexandre','Audrey','Antoine']
            //join ou implode('<br>',$tableau)
            // résultat => Kim<br>Alexandre<br>Audrey<br>Antoine

            $user = $users[rand(0,count($users)-1)];

            // '<p>'lorem1'</p><p>lorem2</p>lorem3<p>lorem4</p>lorem5</p> // comme le premier paramètre n'est qu'un séparateur on doit mettre l'ouverture du p et fermeture en dehors pour le début et la fin

            $ad->setTitle($title)
            //    ->setSlug($slug) => plus nécéssaire car on a fait un slugify dans Ad.php avec la fonction qu'on a appelé iniatializeSlug()
               ->setCoverImage($coverImage)
               ->setIntroduction($introduction)
               ->setContent($content)
               ->setPrice(rand(40,200))
               ->setRooms(rand(1,5))
               ->SetAuthor($user);

               //on doit le mettre dans le for pour faire le lien avec l'ad en cours

               //Gestion de la galerie image de l'annonce
                for($g=1; $g<=rand(2,5);$g++)
                {
                    $image = new Image();
                    $image->setUrl('https://picsum.photos/id/'.$g.'/900')
                          ->setCaption($faker->sentence())
                          ->setAd($ad);
                    $manager->persist($image); // on vient persister les images
                }

                // gestion des réservations
                for($b=1;$b<=rand(0,10);$b++)
                {
                    $booking = new Booking();
                    $createdAt = $faker->dateTimeBetween('-6 months','-4 months');
                    $startDate = $faker->dateTimeBetween('-3 months'); // entre aujourd'hui et il y a 3mois
                    $duration = rand(3,10);
                    // comme c'est un dateTime
                    $endDate = (clone $startDate)->modify("+".$duration." days"); //on vient modifier startDate ( un clone )  pour ajouter la duration à startDate pour faire le endDate
                    $amount = $ad->getPrice() * $duration;
                    $comment = $faker->paragraph();
                    $booker = $users[rand(0,count($users)-1)];

                    $booking->setBooker($booker)
                            ->setAd($ad)
                            ->setStartDate($startDate)
                            ->setEndDate($endDate)
                            ->setCreatedAt($createdAt)
                            ->setAmount($amount)
                            ->setComment($comment);
                    
                    $manager->persist($booking);

                    //gestion des commentaires, on crée un commentaire pour chaque annonce
                    $comment = new Comment();
                    $comment->setContent($faker->paragraph())
                           ->setRating(rand(1,5))
                           ->setAuthor($booker)
                           ->setAd($ad);

                    $manager->persist($comment);
                }

            $manager->persist($ad);
        }
        // $product = new Product();
        // $manager->persist($product);
        $manager->flush();
    }
}
