<?php

declare(strict_types=1);

namespace App\Controller;

use App\Integration\Some\Exception\ExceptionInterface as SomeExceptionInterface;
use App\Integration\Some\ProviderInterface;
use Exception;
use Psr\Log\LoggerInterface;

class LessonController
{
    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @var ProviderInterface
     */
    private $provider;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(ResponseFactory $responseFactory, ProviderInterface $provider, LoggerInterface $logger)
    {
        $this->responseFactory = $responseFactory;
        $this->provider = $provider;
        $this->logger = $logger;
    }

    /**
     * Убрал вообще phpdoc, тк непонятно как сюда данные будут приходить (приходит строка судя по коду), а указание типов может сбить с толку, и о валидации забудут.
     * Использую __invoke так как экшен в контроллере один, а отдельный экшен это лишний семантический шум.
     */
    public function __invoke($categoryId, $responseFormat = 'json'): void
    {
        /**
         * Валидацией входных данных должен заниматься отдельный сервис в идеале ещё до запуска экшена контроллера. А в контроллер будет приходить некая DTO.
         * В symfony приложениях с этим помогает ParamConverter входящий в состав Sensio FrameworkExtraBundle.
         * Регулярное выражение неверное, надо так '/^[1-9]{1}[0-9]{4}$/'
         * - жесткие 5 символов могут сыграть злую шутку, когда уроков станет больше 100k.
         *   Я бы вообще убрал это ограничение, тк скорее всего речь об особом инкременте в бд.
         *   А если имеет место некое правило на идентификатор категории, то надо прикручивать соответствующую валидацию;
         * - отсутствует валидация на лидирующие нули, а раз уж длина критична, то их использование невалидно;
         * - отсутствует валидация на начало и конец строки;
         * - declare(strict_types=1); делает непозволительным неявное приведение типов при проверки на отрицательный int:
         *   Валидируем через регулярное выражение и используем явное приведение типов
         * Необходимо провалидировать $responseFormat заранее
         * Необходимо проверить на тип данных до того как валидировать формат ответа и регулярным выражением id категории
         */

        /**
         * Для входных и выходных данных в провайдер стоит использовать DTO классы
         * из-за незнания контекста не реализовал
         */
        try {
            $data = $this->provider->get([
                'categoryId' => $categoryId,
                'status' => 1,
            ]);
        } catch (SomeExceptionInterface $e) {
            $this->logger->error('Some exception', [ //разумеется, стоит написать что-то более информативное
                'exception' => $e,
            ]);
            echo $this->responseFactory->createFailure();
            return;
        } catch (Exception $e) {
            $this->logger->error('Exception', [ //разумеется, стоит написать что-то более информативное
                'exception' => $e,
            ]);
            echo $this->responseFactory->createFailure();
            return;
        }

        if (empty($data)) {
            echo $this->responseFactory->createFailureNotFound();
            return;
        }

        echo $this->responseFactory->createSuccess($data, $responseFormat);
    }
}