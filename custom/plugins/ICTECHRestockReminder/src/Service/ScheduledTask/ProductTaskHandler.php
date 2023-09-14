<?php

declare(strict_types=1);

namespace ICTECHRestockReminder\Service\ScheduledTask;

use ICTECHRestockReminder\Core\Api\ProductTaskController;
use ICTECHRestockReminder\Service\ProductReminderService;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskDefinition;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskEntity;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;

class ProductTaskHandler extends ScheduledTaskHandler
{
    /**
     * @var EntityRepositoryInterface
     */
    protected $scheduledTaskRepository;
    protected EntityRepositoryInterface $salesChannelRepository;
    protected ProductReminderService $productReminderService;
    private ProductTaskController $productTaskController;

    public function __construct(
        EntityRepositoryInterface $scheduledTaskRepository,
        ProductTaskController $productTaskController,
        EntityRepositoryInterface $salesChannelRepository,
        ProductReminderService $productReminderService
    ) {
        $this->scheduledTaskRepository = $scheduledTaskRepository;
        $this->productTaskController = $productTaskController;
        $this->salesChannelRepository = $salesChannelRepository;
        $this->productReminderService = $productReminderService;

        parent::__construct($scheduledTaskRepository);
    }

    public static function getHandledMessages(): iterable
    {
        return [ ProductTask::class ];
    }

    public function run(): void
    {
        $context = $this->getContext();
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', 'ICTECH.product_task'));
        $getScheduledTask = $this->scheduledTaskRepository->search(
            $criteria,
            $context
        )->first();
        $this->productReminderService->setStatus(
            null,
            $getScheduledTask->getStatus()
        );
        $this->productReminderService->setName(
            null,
            $getScheduledTask->getName()
        );
        $interval = $this->productReminderService->getInterval();
        $data = array();
        if ($interval) {
            $data[] = [
                'id' => $getScheduledTask->getId(),
                'runInterval' => $interval,
            ];
            $this->scheduledTaskRepository->update($data, $context);
            $this->productTaskController->getProducts($this->getContext());
        }
    }

    protected function markTaskRunning(ScheduledTask $task): void
    {
        $this->scheduledTaskRepository->update([
            [
                'id' => $task->getTaskId(),
                'status' => ScheduledTaskDefinition::STATUS_RUNNING,
            ],
        ], $this->getContext());

        $context = $this->getContext();
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', 'ICTECH.product_task'));
        $getScheduledTask = $this->scheduledTaskRepository->search(
            $criteria,
            $context
        )->first();
        $this->productReminderService->setStatus(
            null,
            $getScheduledTask->getStatus()
        );
    }

    protected function markTaskFailed(ScheduledTask $task): void
    {
        $this->scheduledTaskRepository->update([
            [
                'id' => $task->getTaskId(),
                'status' => ScheduledTaskDefinition::STATUS_FAILED,
            ],
        ], $this->getContext());

        $context = $this->getContext();
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', 'ICTECH.product_task'));
        $getScheduledTask = $this->scheduledTaskRepository->search(
            $criteria,
            $context
        )->first();
        $this->productReminderService->setStatus(
            null,
            $getScheduledTask->getStatus()
        );
    }


    protected function rescheduleTask(
        ScheduledTask $task,
        ScheduledTaskEntity $taskEntity
    ): void {
        $now = new \DateTimeImmutable();

        $nextExecutionTimeString = $taskEntity->getNextExecutionTime()->format(
            Defaults::STORAGE_DATE_TIME_FORMAT
        );
        $nextExecutionTime = new \DateTimeImmutable($nextExecutionTimeString);
        $newNextExecutionTime = $nextExecutionTime->modify(
            sprintf('+%d seconds', $taskEntity->getRunInterval())
        );

        if ($newNextExecutionTime < $now) {
            $newNextExecutionTime = $now;
        }

        $this->scheduledTaskRepository->update([
            [
                'id' => $task->getTaskId(),
                'status' => ScheduledTaskDefinition::STATUS_SCHEDULED,
                'lastExecutionTime' => $now,
                'nextExecutionTime' => $newNextExecutionTime,
            ],
        ], $this->getContext());

        $context = $this->getContext();
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', 'ICTECH.product_task'));
        $getScheduledTask = $this->scheduledTaskRepository->search(
            $criteria,
            $context
        )->first();
        $this->productReminderService->setStatus(
            null,
            $getScheduledTask->getStatus()
        );
    }

    private function getContext(): Context
    {
        return Context::createDefaultContext();
    }
}
