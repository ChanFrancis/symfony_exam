<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\Publication;
use App\Entity\Reaction;
use App\Entity\Tag;
use App\Entity\User;
use App\Enum\UserAccountStatusEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public const MAX_USERS = 10;
    public const MAX_COMMENTS = 35;
    public const MAX_PUBLICATIONS = 10;
    public const MAX_TAGS = 5;
    public const MAX_REACTIONS = 10;

    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $users = [];
        $comments = [];
        $publications = [];
        $tags = [];
        $reactions = [];

        $this->createUsers(manager: $manager, users: $users, passwordHasher: $this->passwordHasher);
        $this->createComments($manager, $users, $comments);
        $this->createPublications($manager, $users, $publications, $tags);
        $this->createReactions($manager, $comments, $reactions);

        $manager->flush();
    }

    protected function createUsers(ObjectManager $manager,  UserPasswordHasherInterface $passwordHasher,  array &$users): void
    {
        for ($i = 0; $i < self::MAX_USERS; $i++) {
            $user = new User();
            $user->setEmail(email: "test_$i@example.com");
            $user->setname(name: "test_$i");
            $user->setPlainPassword(plainPassword: 'hello');
            $user->setUserAccountStatus(accountStatus: UserAccountStatusEnum::ACTIVE);
            
            if ($i === 0) {
                $user->setRoles(['ROLE_ADMIN']);
            }
            elseif ($i < 3){  
                $user->setRoles(['ROLE_USER']);
            }
            elseif ($i < (self::MAX_USERS - 1)){
                $user->setRoles(['ROLE_USER']);
            }
            else {
                $user->setRoles(['ROLE_BANNED']);
            }

            $users[] = $user;

            $manager->persist(object: $user);
        }
    }


    protected function createComments(ObjectManager $manager, array $users, array &$comments): void
    {
        foreach ($users as $user) {
            for ($i = 0; $i < self::MAX_COMMENTS; $i++) {
                $comment = new Comment();
                $comment->setComment("This is a comment from user {$user->getName()} #$i");
                $comment->setUser($user);
                $comments[] = $comment;

                $manager->persist($comment);
            }
        }
    }

    protected function createPublications(ObjectManager $manager, array $users, array &$publications, array &$tags): void
    {
        foreach ($users as $user) {
            for ($i = 0; $i < self::MAX_PUBLICATIONS; $i++) {
                $publication = new Publication();
                $publication->setTitle("Publication Title $i");
                $publication->setContent("This is content for publication #$i by user {$user->getName()}.");
                $publication->setUser($user);

                for ($j = 0; $j < self::MAX_TAGS; $j++) {
                    $tag = new Tag();
                    $tag->setTag("Tag $j");
                    $tags[] = $tag;
                    $publication->addTag($tag);
                    $manager->persist($tag);
                }

                $publications[] = $publication;
                $manager->persist($publication);
            }
        }
    }

    protected function createReactions(ObjectManager $manager, array $comments, array &$reactions): void
    {
        foreach ($comments as $comment) {
            for ($i = 0; $i < self::MAX_REACTIONS; $i++) {
                $reaction = new Reaction();
                $reaction->setUser($comment->getUser());

                $reaction->setLiked((bool)random_int(0, 1));
                $reaction->setDisliked((bool)random_int(0, 1));
                $reaction->setComment($comment);

                $reactions[] = $reaction;
                $manager->persist($reaction);
            }
        }
    }
}
