<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Coaster;
use App\Entity\Park;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Countries;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraints\Image;

class CoasterType extends AbstractType
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', options: [
                'label' => 'Nom du coaster',
            ])
            ->add('maxSpeed')
            ->add('length')
            ->add('maxHeight')
            ->add('operating')
            ->add('park', EntityType::class, [
                'class' => Park::class,
                'required' => false,
                'group_by' => function(Park $entity): ?string {
                    return Countries::getName($entity->getCountry(), 'FR_fr');
                }
            ])
            ->add('categories', EntityType::class, [
                'class' => Category::class,
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'query_builder' => function (EntityRepository $er): QueryBuilder {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.name', 'ASC');
                }
            ])

            //->add('imageFileName')
            ->add('image', FileType::class, [
                'mapped' => false, // Ne pas appeler la méthode getImage dans l'entité Coaster
                'required' => false,
                'constraints' => [
                    new Image(
                        maxSize: '2M'
                    )
                ]
            ])
        ;

        if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            $builder->add('published', options: [
                'label' => 'Publier la fiche',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Coaster::class, // Coaster::class = "App\Entity\Coaster"
        ]);
    }
}
