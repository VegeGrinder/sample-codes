<?php

namespace App\Http\Controllers\Modules\JourneyPlanner;

use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use App\Providers\IntegrationProviders\Wc\WcIntegrationProvider;

class OutletManagementController extends Controller
{
    protected $view = 'modules.journey_planner.outlet_management';
    private $wcIntegrationProvider = null;
    private $userRepository = null;
    private $activityType = [
        'call_log' => [
            'label' => 'Call Log',
        ],
        'send_notes' => [
            'label' => 'Note',
        ],
        'sales_order' => [
            'label' => 'Sales Order',
            'document_status' => [
                'pending',
                'processing',
                'completed',
            ],
        ],
        'payment' => [
            'label' => 'Payment',
            'document_status' => [
                'payment',
                'payment-generated',
            ],
        ],
    ];

    public function __construct(
        WcIntegrationProvider $wcIntegrationProvider,
        UserRepository $userRepository
    ) {
        $this->wcIntegrationProvider = $wcIntegrationProvider;
        $this->userRepository = $userRepository;
    }

    public function getViewData($viewRouteParameters)
    {
        $startDate = date("Y-m-d", strtotime("-14 day"));
        $endDate = date('Y-m-d');
        $customerId = request()->customerId;

        $activitiesList = $this->getActivities($customerId, $startDate, $endDate, 'all');

        $customer = $this->wcIntegrationProvider->getCustomerById($customerId);

        $customerInfo = [
            'code' => $customer->user_metas->DebtorCode,
            'name' => trim($customer->first_name . ' '.  $customer->last_name),
            'company_name' => $customer->billing_address->company,
            'avatar' => '',
            'contact' => $customer->billing_address->phone,
            'email' => $customer->billing_address->email,
        ];

        $userAddress = [
            'address_line1' => $customer->billing_address->address_1,
            'address_line2' => $customer->billing_address->address_2,
            'city' => $customer->billing_address->city,
            'postcode' => $customer->billing_address->postcode,
            'state' => $customer->billing_address->state,
            'country' => $customer->billing_address->country,
        ];

        $financialInfo = $this->retrieveFinancialInfo($customerId);

        $preferredContactTime = $this->retrievePreferredContactTime($customerId);

        return [
            'activitiesList' => $activitiesList,
            'customerInfo' => $customerInfo,
            'userAddress' => $userAddress,
            'userRemark' => $customer->user_metas->Remark,
            'preferredContactTime' => $preferredContactTime,
            'financialInfo' => $financialInfo,
            'users' => $this->userRepository->getUsers(),
            'columns' => config('settings.documents.history.all_documents.tables.all_documents.columns'),
            'recordsUrl' => route('documents.history.data', [
                'supplierName' => request()->route('supplierName'),
                'documentType' => 'all',
                'customerId' => request()->route('customerId'),
            ]),
            'exportUrl' => '',
        ];
    }

    /**
     * Count total activity in array
     *
     * @param array $activities
     * @return int
     */
    private function countActivities($activities)
    {
        $values = array_values($activities);
        $count = 0;

        for ($i = 0; $i < count($values); $i++) {
            $count += count($values[$i]);
        }

        return $count;
    }

    /**
     * Get financial info by customer id
     *
     * @return JsonResponse
     */
    public function getFinancialInfo()
    {
        try {
            $customerId = request()->customerId;
            $financialInfo = $this->retrieveFinancialInfo($customerId);

            return response()->json([
                'message' => 'Successful get financial info.',
                'data'    => $financialInfo,
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'error' => 'Unable to get financial info now.',
            ], 500);
        }
    }

    /**
     * Get financial info by customer id via wc
     *
     * @param int $customerId
     * @return array
     */
    private function retrieveFinancialInfo($customerId)
    {
        $customer = $this->wcIntegrationProvider->getCustomerById($customerId);
        $orders = $this->wcIntegrationProvider->getOrders([
            'filter[customer_id]' => $customerId,
            'filter[status]' => 'invoice',
            'custom_order_field_key' => 'Outstanding',
            'custom_order_field_value' => '0',
            'custom_order_field_compare' => '>',
            'custom_order_field_type' => 'decimal(30, 2)',
        ]);
        $creditTerm = $customer->user_metas->CreditTerm;

        $duedateUtc = \DateTimeUtility::getTimestampUtc(date(config('app.timestamp_timestamp_unformat'), strtotime("tomorrow -{$creditTerm} days")));

        $duedateUtcSec = strtotime($duedateUtc);

        $overdue = 0;
        $totalOutstanding = 0;

        foreach ($orders as $order) {
            $totalOutstanding += $order->custom_fields->Outstanding;
            if (strtotime($order->created_at) < $duedateUtcSec) {
                $overdue += $order->custom_fields->Outstanding;
            }
        }

        return [
            'overdue' => ['title' => 'Overdue', 'value' => 'RM ' . number_format($overdue, 2)],
            'outstanding' => ['title' => 'Outstanding', 'value' => 'RM ' . number_format($totalOutstanding, 2)],
            'creditLimit' => ['title' => 'Credit Limit  ', 'value' => '' == $customer->user_metas->CreditLimit ? 'N/A' : 'RM ' . number_format($customer->user_metas->CreditLimit, 2)],
            'creditTerm' => ['title' => 'Credit Term', 'value' =>  '' == $creditTerm ? 'N/A' : $creditTerm . ' days'],
            'Currency' => $customer->user_metas->Currency,
        ];
    }

    /**
     * Get get preferred contact time info
     *
     * @return JsonResponse
     */
    public function getPreferredContactTime()
    {
        try {
            $preferredContactTime = $this->retrievePreferredContactTime(request()->customerId);

            return response()->json([
                'message' => 'Successful get preferred contact time.',
                'data'    => $preferredContactTime,
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'error' => 'Unable to get preferred contact time now.',
            ], 500);
        }
    }

    /**
     * Get preferred contact time from wc
     *
     * @param int $customerId
     * @return object
     */
    private function retrievePreferredContactTime($customerId)
    {
        $result = $this->wcIntegrationProvider->getJourneyPlanCustomerPreferredContactTime($customerId);

        return $result;
    }

    /**
     * update preferred contact time
     * @requires $_REQUEST: mode, data
     *
     * @return JsonResponse
     */
    public function updatePreferredContactTime()
    {
        try {
            $customerId = request()->customerId;

            $result = $this->wcIntegrationProvider->updateJourneyPlanCustomerPreferredContactTime($customerId, request()->all());

            return response()->json([
                'message' => 'updated preferred contact time.',
                'data'    => $result,
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'error' => 'Unable to update preferred contact time now.',
            ], 500);
        }
    }

    /**
     * Get address info
     *
     * @return JsonResponse
     */
    public function getAddress()
    {
        try {
            $customer = $this->wcIntegrationProvider->getCustomerById(request()->customerId);

            $userAddress = [
                'address_line1' => $customer->billing_address->address_1,
                'address_line2' => $customer->billing_address->address_2,
                'city' => $customer->billing_address->city,
                'postcode' => $customer->billing_address->postcode,
                'state' => $customer->billing_address->state,
                'country' => $customer->billing_address->country,
            ];

            return response()->json([
                'message' => 'Successful get address.',
                'data'    => $userAddress,
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'error' => 'Unable to get address now.',
            ], 500);
        }
    }

    /**
     * Update customer address
     * @requires $_REQUEST: address_line1, address_line2, postcode, city, state, country
     *
     * @return JsonResponse
     */
    public function updateAddress()
    {
        try {
            $customer = new \stdClass();
            $customer->id = request()->customerId;
            $customer->billing_address = new \stdClass();
            $customer->billing_address->address_1 = request()->address_line1;
            $customer->billing_address->address_2 = request()->address_line2;
            $customer->billing_address->postcode = request()->postcode;
            $customer->billing_address->city = request()->city;
            $customer->billing_address->state = request()->state;
            $customer->billing_address->country =  request()->country;

            $result = $this->wcIntegrationProvider->updateCustomer($customer);

            return response()->json([
                'message' => 'Updated preferred contact time.',
                'data'    => $result->billing_address,
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'error' => 'Unable to update preferred contact time now.',
            ], 500);
        }
    }

    /**
     * Get remark
     *
     * @return JsonResponse
     */
    public function getRemark()
    {
        try {
            $customer = $this->wcIntegrationProvider->getCustomerById(request()->customerId);

            return response()->json([
                'message' => 'Successful get address.',
                'data'    => ['remark' => $customer->user_metas->Remark],
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'error' => 'Unable to get address now.',
            ], 500);
        }
    }

    /**
     * Update customer remark
     * @requires $_REQUEST: remark
     *
     * @return JsonResponse
     */
    public function updateRemark()
    {
        try {
            $customer = new \stdClass();
            $customer->id = request()->customerId;
            $customer->user_metas = new \stdClass();
            $customer->user_metas->Remark = request()->remark;

            $result = $this->wcIntegrationProvider->updateCustomer($customer);

            return response()->json([
                'message' => 'Updated preferred remark.',
                'data'    => ['remark'=>$result->user_metas->Remark],
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'error' => 'Unable to update remark now.',
            ], 500);
        }
    }

    /**
     * Create call log
     * @requires $_REQUEST: call_time_spent, status, reschedule, rescheduleDate, rescheduleTime, remark
     *
     *
     * @return JsonResponse
     */
    public function createCallLog()
    {
        try {
            $currentUser = app('User')->getUser();
            $repId = app('User')->getId();
            $customerId = request()->customerId;
            $call_time_spent = request()->call_time_spent;
            $status = request()->status;
            $reschedule = request()->reschedule;
            $rescheduleTime = '';

            if ($reschedule) {
                [$day, $month, $year] = explode('/', request()->rescheduleDate);
                $rescheduleTime = \DateTimeUtility::getTimestampUtc(date('Y-m-d H:i:s', strtotime("{$year}-{$month}-{$day} " . request()->rescheduleTime)));
            }
            $remark = request()->remark;

            $journeyPlan = $this->wcIntegrationProvider->getJourneyPlannerDetailsForTelesalesRep(app('User')->getId(), []);
            $timestampScheduled = '';
            $firstScheduleTime = '';

            foreach ($journeyPlan->calls as $date => $calls) {
                if (isset($calls->{$repId}->planned) && in_array($customerId, $calls->{$repId}->planned) && !isset($calls->{$repId}->actual->{$customerId})) {
                    $timestampScheduled = \DateTimeUtility::getTimestampUtc(date('Y-m-d H:i:s', strtotime("{$date} 00:00:00")));
                    break;
                } elseif (isset($calls->{$repId}->planned) && in_array($customerId, $calls->{$repId}->planned) &&
                    isset($calls->{$repId}->actual->{$customerId}) && '' == $firstScheduleTime) {
                    $firstScheduleTime = \DateTimeUtility::getTimestampUtc(date('Y-m-d H:i:s', strtotime("{$date} 00:00:00")));
                }
            }

            if ('' == $timestampScheduled) {
                if ('' == $firstScheduleTime) {
                    return response()->json([
                        'error' => 'Unable to create call log now, do not have scheduled time for this customer.',
                    ], 404);
                } else {
                    $timestampScheduled = $firstScheduleTime;
                }
            }

            $body = [
                'type'=>'call_log',
                'user_id'=>app('User')->getId(),
                'customer_user_id'=>request()->customerId,
                'data'=>[
                    'time_spent' => $call_time_spent,
                    'status' => $status,
                    'reschedule' => $reschedule,
                    'reschedule_time' => $rescheduleTime,
                    'remark' => $remark,
                ],
                'timestamp_scheduled'=> $timestampScheduled,
            ];

            $result = $this->wcIntegrationProvider->createTask(app('User')->getId(), $body);

            $output = [
                'status' => $result->data->status,
                'type' => 'Call Log',
                'time' => \DateTimeUtility::getTimeFormatted($result->timestamp),
                'timestamp' => \DateTimeUtility::getLocalTimestamp($result->timestamp),
                'salesman_name' => trim($currentUser->first_name . ' ' . $currentUser->last_name),
                'reschedule' => $result->data->reschedule ? 'yes' : 'no',
                'rescheduleDate' => '',
                'rescheduleTime' => $task->data->reschedule_time ?? '',
                'calltime' => $result->data->time_spent,
                'remark' => $result->data->remark,
            ];

            return response()->json([
                'message' => 'Created call log.',
                'data'    => $output,
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'error' => 'Unable to create call log now.',
            ], 500);
        }
    }

    public function updateCallLog()
    {
        try {
            $time = request()->time;
            $status = request()->status;
            $reschedule = request()->reschedule;
            $visitTime = request()->visit_time;
            $remark = request()->remark;


            return response()->json([
                'message' => 'Updated call log.',
                'data'    => [], // to be replaced using updated call log
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'error' => 'Unable to update call log now.',
            ], 500);
        }
    }

    /**
     * Create note
     * @requires $_REQUEST: note
     *
     *
     * @return JsonResponse
     */
    public function createNote()
    {
        try {
            $currentUser = app('User')->getUser();
            $note = request()->note;

            $body = [
                'type'=>'send_notes',
                'user_id'=>app('User')->getId(),
                'customer_user_id'=>request()->customerId,
                'data'=>[
                    'remark' => $note,
                ]
            ];

            $result = $this->wcIntegrationProvider->createTask(app('User')->getId(), $body);

            $output = [
                'status' => '',
                'type' => 'Note',
                'time' => \DateTimeUtility::getTimeFormatted($result->timestamp),
                'timestamp' => \DateTimeUtility::getLocalTimestamp($result->timestamp),
                'salesman_name' => trim($currentUser->first_name . ' ' . $currentUser->last_name),
                'reschedule' => 'no',
                'rescheduleDate' => '',
                'rescheduleTime' => '',
                'calltime' => '',
                'remark' => $result->data->remark,
            ];

            return response()->json([
                'message' => 'Created note.',
                'data'    => $output,
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'error' => 'Unable to create note now.',
            ], 500);
        }
    }

    public function updateNote()
    {
        try {
            // in variables format, need test error handling in vue
            $note = request()->note;

            // add process later

            return response()->json([
                'message' => 'Updated note.',
                'data'    => [], // to be replaced using updated note
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'error' => 'Unable to update note now.',
            ], 500);
        }
    }

    /**
     * Get activities by activity type: all, sales_order, payment, call_log, send_notes
     * @requires $_REQUEST: activity_type
     *
     * @return JsonResponse
     */
    public function getActivitiesList()
    {
        try {
            $customerId = request()->customerId;
            $activityType = request()->activityType ?? 'all';

            $startDate = date("Y-m-d", strtotime("-14 day"));
            $endDate = date('Y-m-d');
            $activitiesList = $this->getActivities($customerId, $startDate, $endDate, $activityType);

            // $activityCounts = $this->getCountByActivityType($activitiesList);

            return response()->json([
                'message' => 'Successful get activities.',
                'data'    => $activitiesList
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'error' => 'Unable to get activities now.',
            ], 500);
        }
    }

    /**
     * Get call log / note
     *
     * @param int $customerId
     * @param date $startDate in format yyyy-mm-dd
     * @param date $endDate in format yyyy-mm-dd
     * @param string $activityType acceptable activity type: all, call_log, note
     * @return array
     */
    private function getTasks($customerId, $startDate, $endDate, $activityType)
    {
        $currentUser = app('User')->getUser();
        $taskType = [
            'call_log' => 'Call Log',
            'send_notes' => 'Note',
        ];

        $body = [
            'type' => $activityType,
            'customer_user_id' => $customerId,
            'from_timestamp' => $startDate,
            'to_timestamp' => "{$endDate}T23:59:59Z",
        ];

        $tasks = $this->wcIntegrationProvider->getTask(app('User')->getId(), $body);

        // if error when calling wp api
        if (isset($tasks->data->status) && 404 == $tasks->data->status) {
            return [];
        }

        $result = [];
        foreach ($tasks as $task) {
            $taskDate = \DateTimeUtility::getDateUnformatted($task->timestamp);
            $taskTime = \DateTimeUtility::getTimeFormatted($task->timestamp);

            $result[$taskDate][] = [
                'status' => $task->data->status ?? '',
                'type' => $taskType[$task->type],
                'time' => $taskTime,
                'salesman_name' => trim($currentUser->first_name . ' ' . $currentUser->last_name),
                'reschedule' => $task->data->time_spent ?? 'no',
                'rescheduleDate' => $task->data->reschedule_date ?? '',
                'rescheduleTime' => $task->data->reschedule_time ?? '',
                'calltime' => $task->data->time_spent ?? '',
                'remark' => $task->data->remark ?? '',
            ];
        }

        return $result;
    }

    /**
     * Get sales order or note
     *
     * @param int $customerId
     * @param date $startDate in format yyyy-mm-dd
     * @param date $endDate in format yyyy-mm-dd
     * @param string $activityType acceptable activity type: all, sales_order, payment
     * @return array
     */
    private function getOrders($customerId, $startDate, $endDate, $activityType)
    {
        $currentUser = app('User')->getUser();
        $status = 'pending,processing,completed,payment,payment-generated';
        if ('sales_order' == $activityType) {
            $status = 'pending,processing,completed';
        } elseif ('payment' == $activityType) {
            $status = 'payment,payment-generated';
        }
        $orderType = [
            'payment' => 'Payment',
            'payment-generated' => 'Payment',
            'pending' => 'Sales Order',
            'processing' => 'Sales Order',
            'completed' => 'Sales Order',
        ];

        $ordersWhere = [
            'filter[customer_id]'=>$customerId,
            'filter[status]'=> $status,
        ];

        $orders = $this->wcIntegrationProvider->getOrders($ordersWhere);

        $result = [];
        foreach ($orders as $order) {
            $orderDate = \DateTimeUtility::getDateUnformatted($order->created_at);
            $orderTime = \DateTimeUtility::getTimeFormatted($order->created_at);

            $result[$orderDate][] = [
                'type' => $orderType[$order->status],
                'time' => $orderTime,
                'salesman_name' => trim($currentUser->first_name . ' ' . $currentUser->last_name),
                'item' => $order->id,
                'remark' => 'Payment' == $orderType[$order->status] ? 'been paid' : 'been created' ,
            ];
        }

        return $result;
    }

    /**
     * Get activity by date
     *
     * @param int $customerId
     * @param date $startDate in format yyyy-mm-dd
     * @param date $endDate in format yyyy-mm-dd
     * @param string $activityType acceptable activity type: all, sales_order, payment, call_log, send_notes
     * @return array
     */
    private function getActivities($customerId, $startDate, $endDate, $activityType)
    {
        if ('all' == $activityType) {
            $tasks = $this->getTasks($customerId, $startDate, $endDate, 'call_log,send_notes');
            $orders = $this->getOrders($customerId, $startDate, $endDate, $activityType);
            $result = array_merge_recursive($tasks, $orders);
        } elseif (in_array($activityType, ['call_log','send_notes'])) {
            $result = $this->getTasks($customerId, $startDate, $endDate, $activityType);
        } elseif (in_array($activityType, ['sales_order','payment'])) {
            $result = $this->getOrders($customerId, $startDate, $endDate, $activityType);
        }

        return $result ?? (object) [];
    }

    /**
     * Get activities by type
     *
     * @param array $activitiesList
     * @return array
     */
    private function getCountByActivityType($activitiesList)
    {
        $activityTypeCounts = [
            'Payment' => 0,
            'Sales Order' => 0,
            'Call Log' => 0,
            'Notes' => 0
        ];

        foreach ($activitiesList as $date_key => $activity_value) {
            switch ($activity_value->type) {
                case 'Sales Order':
                    $activityTypeCounts['sales_order']++;
                    break;
                case 'Payment':
                    $activityTypeCounts['payment']++;
                    break;
                case 'Call Log':
                    $activityTypeCounts['call_log']++;
                    break;
                case 'Notes':
                    $activityTypeCounts['notes']++;
                    break;
            }
        }

        return $activityTypeCounts;
    }
}
