<?php 

namespace Senku\Commands\Messages;

use Mateodioev\Bots\Telegram\{Buttons, Methods};
use Mateodioev\TgHandler\Commands;
use Mateodioev\Utils\fakeStdClass;
use Senku\Commands\Plugins\GithubApi;

use function Mateodioev\Senku\{b, code, i, n};

class Github extends Message
{
  public const GITHUB_API = '';

  protected function notFound(string $username): string
  {
    return b('User "' . i($username) . '" not found');
  }

  protected function getText(fakeStdClass $git): string
  {
    $git->setReturnValue('0');

    return b('Username: ') . i($git->login) . ' (' . code($git->id) . ')' .
      n() . b('Bio: ') . i($git->bio) .
      n() . b('Website: ') . i($git->blog) .
      n() . b('Company: ') . i($git->company) .
      n() . b('Twitter: ') . i($git->twitter_username) .
      n() . b('Repos/gits: ') . i($git->public_repos . '/' . $git->public_gits) .
      n() . b('Followers: ') . i($git->followers);
  }

  protected function getButton(array $repos): string
  {
    $b = Buttons::create();

    foreach ($repos as $i => $rep) {
      if ($i == 15) break;

      $b->addCeil([
        'text' => $rep['language'] . ' - ' . $rep['name'],
        'url'  => $rep['html_url']
      ]);

      if ($i % 2 == 0 && $i > 0) $b->AddLine();
    }

    return $b->addCeil(['text' => 'Github Profile', 'url' => $repos[0]['owner']['html_url']]);
  }

  public function start(Methods $bot, Commands $cmd): fakeStdClass
  {
    $this->addReply($bot, $cmd);
    $user = $cmd->getPayload();

    if (empty($user)) {
      return $this->sendDefaultEmpty($bot, $cmd, 'Github user search', 'username');
    }

    $data = GithubApi::getInstance()->getUser($user);

    if ($data->message != '') {
      return $bot->sendMessage($cmd->getChatId(), $this->notFound($user));
    }

    $repos = GithubApi::getInstance()->getRepos($data->login);

    return $bot->AddOpt(['reply_markup' => $this->getButton($repos)])->sendDocument([
      'document' => $data->avatar_url,
      'caption' => $this->getText($data)
    ]);
  }
}
