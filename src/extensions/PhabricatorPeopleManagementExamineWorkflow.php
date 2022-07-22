<?php

final class PhabricatorPeopleManagementExamineWorkflow
  extends PhabricatorPeopleManagementWorkflow {

  protected function didConstruct() {
    $arguments = $this->getUserSelectionArguments();

    $arguments[] = array(
      'name' => 'projects',
      'help' => pht(
        'List projects in which the user is a member.'),
    );

    $arguments[] = array(
      'name' => 'repositories',
      'help' => pht(
        'List repositories that include the user in their view or push policies.'),
    );

    $this
      ->setName('examine')
      ->setExamples(
        "**examine** --user __username__ --projects\n".
        "**examine** --user __username__ --repositories")
      ->setSynopsis(pht('Examine project memberships or repository policies.'))
      ->setArguments($arguments);
  }

  public function execute(PhutilArgumentParser $args) {
    $modes = array();

    $is_projects = $args->getArg('projects');
    if ($is_projects) {
      $modes[] = 'projects';
    }

    $is_repositories = $args->getArg('repositories');
    if ($is_repositories) {
      $modes[] = 'repositories';
    }

    if (count($modes) > 1) {
      throw new PhutilArgumentUsageException(
        pht(
          'You have selected multiple operation modes (%s). Choose a '.
          'single mode to operate in.',
          implode(', ', $modes)));
    }

    $user = $this->selectUser($args);

    if ($is_projects) {
      $projects = id(new PhabricatorProjectQuery())
        ->setViewer($this->getViewer())
        ->withMemberPHIDs(array($user->getPHID()))
        ->execute();

      ksort($projects);

      foreach ($projects as $project_id => $project) {
        $project_info = '['.$project_id.'] '.$project->getName();

        if ($project->supportsEditMembers()) {
          $this->logOkay(pht('MEMBER'), $project_info);
        }
      }
    } else if ($is_repositories) {
      $repositories = id(new PhabricatorRepositoryQuery())
        ->setViewer($user)
        ->execute();

      ksort($repositories);

      foreach ($repositories as $repository_id => $repository) {
        $repository_info = '['.$repository_id.'] '.$repository->getName();

        $can_push = PhabricatorPolicyFilter::hasCapability(
          $user,
          $repository,
          DiffusionPushCapability::CAPABILITY);

        if ($can_push) {
          $this->logWarn(pht('PUSH'), $repository_info);
        } else {
          $this->logOkay(pht('VIEW'), $repository_info);
        }
      }
    }

    return 0;
  }

}
