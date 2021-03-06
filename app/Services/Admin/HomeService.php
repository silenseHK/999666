<?php


namespace App\Services\Admin;


use App\Repositories\Admin\HomeRepository;
use App\Services\BaseService;

class HomeService extends BaseService
{
    private $HomeRepository;

    public function __construct(HomeRepository $homeRepository)
    {
        $this->HomeRepository = $homeRepository;
    }

    public function findAll()
    {
        $timeMap = [strtotime(date("Y-m-d 00:00:00")), strtotime(date("Y-m-d 23:59:59"))];
        $this->_data = $this->getContext($timeMap, $this->HomeRepository->getIds());
    }

    public function searchHome($data)
    {
        if (array_key_exists("timeMap", $data) && $data["timeMap"]) {
            $timeMap = $data["timeMap"];
        } else {
            $timeMap = [strtotime(date("Y-m-d 00:00:00")), strtotime(date("Y-m-d 23:59:59"))];
        }

        if (array_key_exists("reg_source_id", $data)) {
            $ids = $this->HomeRepository->getRegSourceIds($data["reg_source_id"]);
        } else {
            $ids = $this->HomeRepository->getIds();
        }
        $this->_data = $this->getContext($timeMap, $ids);
    }

    public function getContext($timeMap, $ids)
    {
        $item = new \stdClass();
        // 会员数
        $item->members = $this->HomeRepository->countMembers($ids);
        // 新增会员
        $item->newMembers = $this->HomeRepository->countNewMembers($timeMap, $ids);
        // 普通新增会员
        $item->ordinaryMembers = $this->HomeRepository->countOrdinaryMembers($timeMap, $ids);
        // 代理裂变会员
        $item->agentMembers = $this->HomeRepository->countAgentMembers($timeMap, $ids);
        // 红包裂变会员
        $item->envelopeMembers = $this->HomeRepository->countEnvelopeMembers($timeMap, $ids);
        // 活跃人数
        $item->activePeopleNumber = $this->HomeRepository->countActivePeopleNumber($timeMap, $ids);
        // 首充人数
        $item->firstChargeNumber = $this->HomeRepository->countFirstChargeNumber($timeMap, $ids);
        // 普通首充
        $item->ordinaryFirstChargeNumber = $this->HomeRepository->countOrdinaryFirstChargeNumber($timeMap, $ids);
        // 代理首充
        $item->agentFirstChargeNumber = $this->HomeRepository->countAgentFirstChargeNumber($timeMap, $ids);
        // 充值金额
        $item->rechargeMoney = $this->HomeRepository->sumRechargeMoney($ids, $timeMap);
        // 提现金额
        $item->withdrawalMoney = $this->HomeRepository->sumWithdrawalMoney($ids, $timeMap);
        // 待提现金额
        $item->toBeWithdrawalMoney = bcadd($this->HomeRepository->sumUserBalance($ids), $this->HomeRepository->sumUserCommission($ids), 2);
        // 订单分佣
        $item->subCommission = $this->HomeRepository->sumSubCommission($ids, $timeMap);
        // 赠金
        $item->giveMoney = $this->HomeRepository->sumGiveMoney($ids, $timeMap);
        // 购买签到礼包
        $item->payEnvelope = $this->HomeRepository->countPayEnvelope($ids, $timeMap);
        // 领取签到礼包
        $item->receiveEnvelope = $this->HomeRepository->sumReceiveEnvelope($ids, $timeMap);
        // 订单数
        $item->bettingNumber = $this->HomeRepository->countBettingNumber($ids, $timeMap);
        // 下单金额
        $item->bettingMoney = $this->HomeRepository->sumBettingMoney($ids, $timeMap);
        // 总服务费
        $item->serviceMoney = $this->HomeRepository->sumServiceMoney($ids, $timeMap);
        // 购买签到礼包金额
        $item->payEnvelopeAmount = $this->HomeRepository->sumPayEnvelope($ids, $timeMap);
        // 用户投注盈利
        $item->userProfit = $this->HomeRepository->sumUserProfit($ids, $timeMap);
        // 平台服务费
        $item->platformServiceMoney = bcsub($item->serviceMoney, $item->subCommission, 2);
        // 总盈亏
        $item->totalProfitLoss = bcadd(bcsub($item->bettingMoney, $item->userProfit, 2), $item->platformServiceMoney, 2);
        // 后台赠送礼金
        $item->backstageGiftMoney = $this->HomeRepository->sumBackstageGiftMoney($ids, $timeMap);
        // 当日上方
        $item->upperSeparation = $this->HomeRepository->sumUpperSeparation($ids, $timeMap);
        // 当日下分
        $item->downSeparation = $this->HomeRepository->sumDownSeparation($ids, $timeMap);
        return $item;
    }
}
