<?php
declare(strict_types=1);
namespace EliasHaeussler\CacheWarmup\Crawler;

/*
 * This file is part of the Composer package "eliashaeussler/cache-warmup".
 *
 * Copyright (C) 2020 Elias Häußler <elias@haeussler.dev>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * OutputtingCrawler
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
class OutputtingCrawler extends ConcurrentCrawler
{
    protected const PROGRESS_BAR_FORMAT = ' %current%/%max% [%bar%] %percent:3s%% -- %url% %state%';

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var ProgressBar
     */
    protected $progress;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
        ProgressBar::setFormatDefinition('cache-warmup', self::PROGRESS_BAR_FORMAT);
    }

    public function crawl(array $urls): void
    {
        $this->startProgressBar(count($urls));
        parent::crawl($urls);
        $this->progress->finish();
        $this->output->writeln('');
    }

    public function onSuccess(ResponseInterface $response, int $index): void
    {
        $this->progress->setMessage((string)$this->urls[$index], 'url');
        $this->progress->setMessage('(<info>success</info>)', 'state');
        $this->progress->advance();
        $this->progress->display();
        parent::onSuccess($response, $index);
    }

    public function onFailure(\Throwable $exception, int $index): void
    {
        $this->progress->setMessage((string)$this->urls[$index], 'url');
        $this->progress->setMessage('(<error>failed</error>)', 'state');
        $this->progress->advance();
        $this->progress->display();
        parent::onFailure($exception, $index);
    }

    protected function startProgressBar(int $max): void
    {
        $this->progress = new ProgressBar($this->output, $max);
        $this->progress->setFormat('cache-warmup');
        $this->progress->setOverwrite(false);
        $this->progress->setMessage('', 'url');
        $this->progress->setMessage('', 'state');
    }
}