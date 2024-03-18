<?php
/**
 * Copyright (c) 2023, elio GmbH.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 *
 * 3. Neither the name of the copyright holder nor the names of its contributors
 * may be used to endorse or promote products derived from this software without
 * specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace Elio\ElioSearch\Core\Sync\Output;


use Elio\ElioSearch\Core\Sync\Exception\OutputNotFoundException;
use Elio\ElioSearch\Core\Sync\ProfileInterface;
use Elio\ElioSearch\Core\Sync\SyncContext;

/**
 * Class OutputService
 * @package Elio\ElioSearch\Core\Sync\Output
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2023, elio GmbH (https://www.elio-systems.com)
 */
class OutputService
{
    public function __construct(
        private readonly iterable $outputs
    ) {}

    public function createOutputStream(SyncContext $syncContext): OutputStream
    {
        return new OutputStream(
            $this->getOutputs($syncContext->getProfileDefinition()),
            $syncContext
        );
    }

    /**
     * @param ProfileInterface $profile
     * @return OutputInterface[]
     */
    protected function getOutputs(ProfileInterface $profile): array
    {
        $outputs = [];

        foreach ($profile->getOutputs() as $output) {
            $outputs[] = $this->getOutput($output);
        }

        return $outputs;
    }

    /**
     * Gets profile api writer
     *
     * @param string $name
     * @return OutputInterface
     */
    protected function getOutput(string $name): OutputInterface
    {
        /** @var OutputInterface $output */
        foreach ($this->outputs as $output) {
            if ($output->supports($name)) {
                return $output;
            }
        }

        throw new OutputNotFoundException(sprintf('Output "%s" not found', $name));
    }
}