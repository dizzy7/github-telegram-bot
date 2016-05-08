<?php

namespace AppBundle\Service\Command;

use AppBundle\Entity\Repository;
use AppBundle\Entity\TagsSubsription;
use AppBundle\Entity\User;
use AppBundle\Interfaces\TelegramCommand;
use Doctrine\ORM\EntityManager;
use Github\Client;
use TelegramBot\Api\BotApi;

class TagsCommand implements TelegramCommand
{
    private $em;
    private $github;
    private $botapi;

    public function __construct(EntityManager $em, Client $github, BotApi $botApi)
    {
        $this->em = $em;
        $this->github = $github;
        $this->botapi = $botApi;
    }

    public function isCommandMatch($command)
    {
        return preg_match('#^https://github.com/[^/]+/[^/]+/releases$#', $command);
    }

    public function execute(User $user, $command)
    {
        $matches = [];
        preg_match('#^https://github.com/([^/]+)/([^/]+)/releases$#', $command, $matches);
        $githubUser = $matches[1];
        $repository = $matches[2];

        $repository = $this->getRepository($githubUser, $repository);
        $result = $this->addSubscription($user, $repository);
        if ($result) {
            return 'New tags notification added';
        } else {
            return 'New tags notification removed';
        }
    }

    public function checkUpdates()
    {
        $repositories = $this->em->getRepository(Repository::class)->findAll();
        foreach ($repositories as $repository) {
            if ($repository->getTagsSubscribers()->count() > 0) {
                $newTags = $this->checkRepositoryNewTags($repository);
                $this->addNewTags($repository, $newTags);
                $this->notifyNewTags($repository, $newTags);
            }
        }

        $this->em->flush();
    }

    private function getRepository($user, $repositoryName)
    {
        $repository = $this->em->getRepository(Repository::class);
        $repositoryEntity = $repository->findOneBy(['user' => $user, 'repository' => $repositoryName]);
        if ($repositoryEntity === null) {
            $repositoryEntity = new Repository();
            $repositoryEntity->user = $user;
            $repositoryEntity->repository = $repositoryName;
            $tags = $this->checkRepositoryNewTags($repositoryEntity);
            $repositoryEntity->tags = $tags;
            $this->em->persist($repositoryEntity);
        }

        return $repositoryEntity;
    }

    private function addSubscription(User $user, Repository $repository)
    {
        $subscriptionRepository = $this->em->getRepository(TagsSubsription::class);
        $subscription = $subscriptionRepository->findOneBy(['user' => $user, 'repository' => $repository]);
        if ($subscription) {
            $this->em->remove($subscription);
            $this->em->flush();

            return false;
        }

        $subscription = new TagsSubsription();
        $subscription->setUser($user);
        $subscription->setRepository($repository);
        $this->em->persist($subscription);
        $this->em->flush();

        return true;
    }

    private function checkRepositoryNewTags(Repository $repository)
    {
        $githubTags = $this->github->api('repo')->tags($repository->user, $repository->repository);
        $githubTags = array_map(
            function ($tag) {
                return $tag['name'];
            },
            $githubTags
        );
        $tags = $repository->tags;

        return array_diff($githubTags, $tags);
    }

    private function addNewTags(Repository $repository, $newTags)
    {
        foreach ($newTags as $newTag) {
            $repository->tags[] = $newTag;
        }
    }

    private function notifyNewTags(Repository $repository, $newTags)
    {
        foreach ($newTags as $newTag) {
            foreach ($repository->getTagsSubscribers() as $subscriber) {
                $this->botapi->sendMessage(
                    $subscriber->getUser()->getId(),
                    sprintf(
                        'New release in https://www.github.com/%s/%s : %s',
                        $repository->user,
                        $repository->repository,
                        $newTag
                    ),
                    null,
                    true
                );
            }
        }
    }
}