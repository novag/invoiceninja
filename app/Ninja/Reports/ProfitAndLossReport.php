<?php

namespace App\Ninja\Reports;

use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use Auth;

class ProfitAndLossReport extends AbstractReport
{
    public function getColumns()
    {
        return [
            'date' => [],
            'type' => [],
            'notes' => [],
            'client' => [],
            'vendor' => [],
            'amount' => [],
        ];
    }

    public function run()
    {
        $account = Auth::user()->account;
        $subgroup = $this->options['subgroup'];

        $payments = Payment::scope()
                        ->orderBy('payment_date', 'desc')
                        ->with('client.contacts', 'invoice', 'user')
                        ->withArchived()
                        ->excludeFailed()
                        ->where('payment_date', '>=', $this->startDate)
                        ->where('payment_date', '<=', $this->endDate);

        foreach ($payments->get() as $payment) {
            $client = $payment->client;
            $invoice = $payment->invoice;
            if ($client->is_deleted || $invoice->is_deleted) {
                continue;
            }

            $this->data[] = [
                $this->isExport ? $payment->payment_date : $payment->present()->payment_date,
                trans('texts.payment'),
                $payment->present()->method,
                $client ? ($this->isExport ? $client->getDisplayName() : $client->present()->link) : '',
                '',
                $account->formatMoney($payment->getCompletedAmount(), $client),
            ];

            $this->addToTotals($client->currency_id, 'revenue', $payment->getCompletedAmount(), $payment->present()->month);
            $this->addToTotals($client->currency_id, 'vat', $payment->taxAmount(), $payment->present()->month);
            $this->addToTotals($client->currency_id, 'expenses', 0, $payment->present()->month);
            $this->addToTotals($client->currency_id, 'inputtax', 0, $payment->present()->month);
            $this->addToTotals($client->currency_id, 'profit', $payment->getCompletedNetAmount(), $payment->present()->month);

            if ($subgroup == 'type') {
                $dimension = trans('texts.payment');
            } else {
                $dimension = $this->getDimension($payment);
            }
            $this->addChartData($dimension, $payment->payment_date, $payment->getCompletedNetAmount());
        }

        $expenses = Expense::scope()
                        ->orderBy('expense_date', 'desc')
                        ->with('client.contacts', 'vendor')
                        ->withArchived()
                        ->where('expense_date', '>=', $this->startDate)
                        ->where('expense_date', '<=', $this->endDate);

        foreach ($expenses->get() as $expense) {
            $client = $expense->client;
            $vendor = $expense->vendor;
            $this->data[] = [
                $this->isExport ? $expense->expense_date : $expense->present()->expense_date,
                trans('texts.expense'),
                $expense->present()->category,
                $client ? ($this->isExport ? $client->getDisplayName() : $client->present()->link) : '',
                $vendor ? ($this->isExport ? $vendor->name : $vendor->present()->link) : '',
                '-' . $expense->present()->amount,
            ];

            $this->addToTotals($expense->expense_currency_id, 'revenue', 0, $expense->present()->month);
            $this->addToTotals($expense->expense_currency_id, 'vat', 0, $expense->present()->month);
            $this->addToTotals($expense->expense_currency_id, 'expenses', $expense->amountWithTax(), $expense->present()->month);
            $this->addToTotals($expense->expense_currency_id, 'inputtax', $expense->taxAmount(), $expense->present()->month);
            $this->addToTotals($expense->expense_currency_id, 'profit', $expense->amount * -1, $expense->present()->month);

            if ($subgroup == 'type') {
                $dimension = trans('texts.expense');
            } else {
                $dimension = $this->getDimension($expense);
            }
            $this->addChartData($dimension, $expense->expense_date, $expense->amount * -1);
        }
    }
}
