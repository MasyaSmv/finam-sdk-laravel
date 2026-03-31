<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Tests;

use DateTimeImmutable;
use MasyaSmv\FinamSdk\Dto\Auth\AuthRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\AllAssetsRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\AssetsRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\ClockRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\ExchangesRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\GetAssetRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\GetAssetParamsRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\OptionsChainRequest;
use MasyaSmv\FinamSdk\Dto\Market\CandlesQueryDto;
use MasyaSmv\FinamSdk\Dto\Market\CandlesRequest;
use MasyaSmv\FinamSdk\Dto\Market\QuotesRequest;
use MasyaSmv\FinamSdk\Dto\Order\CancelOrderRequest;
use MasyaSmv\FinamSdk\Dto\Order\OrdersRequest;
use MasyaSmv\FinamSdk\Dto\Order\PlaceOrderInputDto;
use MasyaSmv\FinamSdk\Dto\Order\PlaceOrderRequest;
use MasyaSmv\FinamSdk\Dto\Report\CreateAccountReportInputDto;
use MasyaSmv\FinamSdk\Dto\Report\CreateAccountReportRequest;
use MasyaSmv\FinamSdk\Dto\Report\GetAccountReportInfoRequest;
use MasyaSmv\FinamSdk\Dto\Report\ReportDateRangeDto;
use MasyaSmv\FinamSdk\Exceptions\InvalidRequestException;

final class RequestDtoTest extends TestCase
{
    public function testAssetsRequestReturnsEmptyQuery(): void
    {
        $request = new AssetsRequest();

        $this->assertSame([], $request->toQuery());
    }

    public function testAllAssetsRequestBuildsTypedQuery(): void
    {
        $request = new AllAssetsRequest(cursor: 42, onlyActive: true);

        $this->assertSame(42, $request->cursor());
        $this->assertTrue($request->onlyActive());
        $this->assertFalse($request->onlyDisabled());
        $this->assertSame(
            [
                'cursor' => 42,
                'only_active' => true,
            ],
            $request->toQuery(),
        );
    }

    public function testAllAssetsRequestRejectsConflictingFlags(): void
    {
        $this->expectException(InvalidRequestException::class);

        new AllAssetsRequest(cursor: null, onlyActive: true, onlyDisabled: true);
    }

    public function testAuthRequestBuildsPayload(): void
    {
        $request = new AuthRequest('secret-key');

        $this->assertSame('secret-key', $request->secret());
        $this->assertSame(['secret' => 'secret-key'], $request->toPayload());
    }

    public function testAuthRequestRejectsEmptySecret(): void
    {
        $this->expectException(InvalidRequestException::class);

        new AuthRequest('');
    }

    public function testClockRequestReturnsEmptyQuery(): void
    {
        $request = new ClockRequest();

        $this->assertSame([], $request->toQuery());
    }

    public function testExchangesRequestReturnsEmptyQuery(): void
    {
        $request = new ExchangesRequest();

        $this->assertSame([], $request->toQuery());
    }

    public function testGetAssetParamsRequestMovesSymbolToPathAndKeepsOnlyAccountInQuery(): void
    {
        $request = new GetAssetParamsRequest('SBER@MISX', 'account-1');

        $this->assertSame('SBER@MISX', $request->symbol());
        $this->assertSame(['account_id' => 'account-1'], $request->toQuery());
    }

    public function testGetAssetRequestMovesSymbolToPathAndKeepsOptionalAccountInQuery(): void
    {
        $request = new GetAssetRequest('GAZP@MISX', 'account-1');

        $this->assertSame('GAZP@MISX', $request->symbol());
        $this->assertSame(['account_id' => 'account-1'], $request->toQuery());
    }

    public function testOptionsChainRequestMovesUnderlyingSymbolToPath(): void
    {
        $request = new OptionsChainRequest(
            underlyingSymbol: 'YDEX@MISX',
            root: 'WEEKLY',
            expirationDate: '2026-04-03',
        );

        $this->assertSame('YDEX@MISX', $request->underlyingSymbol());
        $this->assertSame(
            [
                'root' => 'WEEKLY',
                'expiration_date' => '2026-04-03',
            ],
            $request->toQuery(),
        );
    }

    public function testQuotesRequestUsesPathSymbolAndNoQueryParameters(): void
    {
        $request = new QuotesRequest('SBER@MISX');

        $this->assertSame('SBER@MISX', $request->symbol());
        $this->assertSame([], $request->toQuery());
    }

    public function testQuotesRequestRejectsEmptySymbol(): void
    {
        $this->expectException(InvalidRequestException::class);

        new QuotesRequest('');
    }

    public function testCandlesRequestLeavesSymbolInPathAndOnlySerializesQueryFields(): void
    {
        $request = new CandlesRequest(
            new CandlesQueryDto(
                symbol: 'SBER@MISX',
                timeframe: 'TIME_FRAME_M1',
                startDate: new DateTimeImmutable('2026-03-31T10:00:00+03:00'),
                endDate: new DateTimeImmutable('2026-03-31T11:00:00+03:00'),
            ),
        );

        $this->assertSame('SBER@MISX', $request->symbol());
        $this->assertSame(
            [
                'timeframe' => 'TIME_FRAME_M1',
                'interval' => [
                    'start' => 1774940400,
                    'end' => 1774944000,
                ],
            ],
            $request->toQuery(),
        );
    }

    public function testOrdersRequestReturnsEmptyQuery(): void
    {
        $request = new OrdersRequest('account-1');

        $this->assertSame('account-1', $request->accountId());
        $this->assertSame([], $request->toQuery());
    }

    public function testCancelOrderRequestDoesNotBuildPayload(): void
    {
        $request = new CancelOrderRequest('account-1', 'order-1');

        $this->assertSame('account-1', $request->accountId());
        $this->assertSame('order-1', $request->orderId());
        $this->assertSame([], $request->toPayload());
    }

    public function testPlaceOrderRequestBuildsPayloadFromInputDto(): void
    {
        $request = new PlaceOrderRequest(
            accountId: 'account-1',
            payload: new PlaceOrderInputDto(
                symbol: 'SBER@MISX',
                quantity: '10',
                side: 'SIDE_BUY',
                type: 'ORDER_TYPE_LIMIT',
                timeInForce: 'TIME_IN_FORCE_DAY',
                limitPrice: '250.10',
            ),
        );

        $this->assertSame('account-1', $request->accountId());
        $this->assertSame(
            [
                'symbol' => 'SBER@MISX',
                'quantity' => '10',
                'side' => 'SIDE_BUY',
                'type' => 'ORDER_TYPE_LIMIT',
                'time_in_force' => 'TIME_IN_FORCE_DAY',
                'limit_price' => '250.10',
            ],
            $request->toPayload(),
        );
    }

    public function testCreateAccountReportRequestBuildsPayload(): void
    {
        $request = new CreateAccountReportRequest(
            new CreateAccountReportInputDto(
                accountId: '1899011',
                reportForm: 'REPORT_FORM_XLSX',
                dateRange: new ReportDateRangeDto(
                    from: new DateTimeImmutable('2026-03-01'),
                    to: new DateTimeImmutable('2026-03-31'),
                ),
            ),
        );

        $this->assertSame(
            [
                'account_id' => '1899011',
                'report_form' => 'REPORT_FORM_XLSX',
                'date_range' => [
                    'from' => '2026-03-01',
                    'to' => '2026-03-31',
                ],
            ],
            $request->toPayload(),
        );
    }

    public function testGetAccountReportInfoRequestRejectsEmptyReportId(): void
    {
        $this->expectException(InvalidRequestException::class);

        new GetAccountReportInfoRequest('');
    }
}
