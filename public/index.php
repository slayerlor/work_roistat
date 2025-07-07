<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\AmoCRM\Client;
use App\AmoCRM\LeadBuilder;
use App\AmoCRM\ContactBuilder;
use App\AmoCRM\LeadService;
use App\AmoCRM\ContactService;

$client = new Client(
    getenv('AMO_BASE_DOMAIN'),
    getenv('AMO_CLIENT_ID'),
    getenv('AMO_ACCESS_TOKEN')
);

$leadService = new LeadService($client);
$contactService = new ContactService($client);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $price = (float)$_POST['price'];
        $spent30 = $_POST['spent30'] === 'true';
        $customFieldId = (int)getenv('AMO_CUSTOM_FIELD_ID_TIME');

        $lead = (new LeadBuilder())
            ->setPrice($price);

        if ($customFieldId > 0) {
            $lead->setTimeSpentFlag($spent30, $customFieldId);
        }

        $leadId = $leadService->create($lead->build());

        $contact = (new ContactBuilder())
            ->setName($name)
            ->setEmail($email)
            ->setPhone($phone)
            ->build();

        $contactId = $contactService->create($contact);

        $contactService->linkToLead($contactId, $leadId);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Заявка успешно отправлена!',
            'leadId' => $leadId,
            'contactId' => $contactId
        ]);
        exit;
    } catch (\Throwable $e) {
        error_log('ERROR: ' . $e->getMessage());

        header('HTTP/1.1 500 Internal Server Error');
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Ошибка при отправке заявки. Попробуйте позже.',
            'error' => $e->getMessage()
        ]);
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Заявка</title>
    <style>
        :root {
            --color-primary: #2563eb;
            --color-success: #10b981;
            --color-error: #ef4444;
            --color-text: #1f2937;
            --color-bg: #f9fafb;
        }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            line-height: 1.6;
            color: var(--color-text);
            background-color: var(--color-bg);
            max-width: 500px;
            margin: 0 auto;
            padding: 2rem;
        }

        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 2rem;
        }

        h1 {
            margin-top: 0;
            font-weight: 600;
            font-size: 1.75rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
            display: flex;
            flex-direction: column;
        }

        form {
            width: auto;
            display: flex;
            flex-direction: column;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        input {
            width: auto;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 1rem;
        }

        button {
            background: var(--color-primary);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        button:hover {
            background: #1d4ed8;
        }

        button:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }

        .alert {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .timer {
            margin-top: 1rem;
            font-size: 0.875rem;
            color: #6b7280;
        }

        .timer.active {
            color: var(--color-success);
            font-weight: 500;
        }

        .spinner {
            display: inline-block;
            width: 1.2rem;
            height: 1.2rem;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
            margin-right: 0.5rem;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>
    <div class="card">
        <h1>Оставьте заявку</h1>

        <div id="message-container"></div>

        <form id="lead-form" method="post">
            <div class="form-group">
                <label for="name">Имя</label>
                <input type="text" id="name" name="name" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="phone">Телефон</label>
                <input type="tel" id="phone" name="phone" required>
            </div>

            <div class="form-group">
                <label for="price">Цена</label>
                <input type="number" id="price" name="price" min="0" step="1" required>
            </div>

            <input type="hidden" name="spent30" id="spent30" value="false">

            <button type="submit" id="submit-btn">
                <span id="spinner" class="spinner" style="display: none;"></span>
                <span id="btn-text">Отправить заявку</span>
            </button>

            <div class="timer" id="timer">
                Время на странице: <span id="time-counter">0</span> сек.
            </div>
        </form>
    </div>

    <script>
        const form = document.getElementById('lead-form');
        const messageContainer = document.getElementById('message-container');
        const submitBtn = document.getElementById('submit-btn');
        const btnText = document.getElementById('btn-text');
        const spinner = document.getElementById('spinner');
        const spentInput = document.getElementById('spent30');
        const timeCounter = document.getElementById('time-counter');
        const timerElement = document.getElementById('timer');

        const startTime = Date.now();

        function updateTimer() {
            const seconds = Math.floor((Date.now() - startTime) / 1000);
            timeCounter.textContent = seconds;

            if (seconds >= 30 && spentInput.value !== 'true') {
                spentInput.value = 'true';
                timerElement.classList.add('active');
            }
        }

        setInterval(updateTimer, 1000);

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            spinner.style.display = 'inline-block';
            btnText.textContent = 'Отправка...';
            submitBtn.disabled = true;

            messageContainer.innerHTML = '';

            try {
                const formData = new FormData(form);

                const response = await fetch('/', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showMessage(result.message, 'success');
                    form.reset();
                } else {
                    showMessage(result.message, 'error');
                }
            } catch (error) {
                showMessage('Сетевая ошибка. Проверьте соединение.', 'error');
                console.error('Ошибка при отправке:', error);
            } finally {
                spinner.style.display = 'none';
                btnText.textContent = 'Отправить заявку';
                submitBtn.disabled = false;
            }
        });

        function showMessage(text, type) {
            const messageElement = document.createElement('div');
            messageElement.className = `alert alert-${type}`;
            messageElement.textContent = text;
            messageContainer.appendChild(messageElement);

            setTimeout(() => {
                messageElement.style.opacity = '0';
                setTimeout(() => {
                    messageElement.remove();
                }, 300);
            }, 5000);
        }

        updateTimer();
    </script>
</body>

</html>