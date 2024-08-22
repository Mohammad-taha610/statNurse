<?php

namespace nst\member;


/**
 * @Entity(repositoryClass="ProviderPayRateRepository")
 * @InheritanceType("SINGLE_TABLE")
 * @Table="provider_pay_rates"
 */
class ProviderPayRate {

    /**
     * @var int $id
     * @Id @GeneratedValue @Column(type="integer")
     */
    protected $id;
    
    /**
     * @var Provider $provider
     * @ManyToOne(targetEntity="nst\member\Provider", inversedBy="pay_rates")
     */
    protected $provider;

    /**
     * @var string $nurse_type
     * @Column(type="string", nullable=true)
     */
    protected $nurse_type;

    /**
     * @var string $rate_type
     * @Column(type="string", nullable=true)
     */
    protected $rate_type;

    /**
     * @var bool $is_covid
     * @Column(type="boolean", nullable=true)
     */
    protected $is_covid;

    /**
     * @var float $incentive
     * @Column(type="float", nullable=true)
     */
    protected $incentive;

    /**
     * @var string $pay_or_bill
     * @Column(type="string", nullable=true)
     */
    protected $pay_or_bill;

    /**
     * @var float $rate
     * @Column(type="float", nullable=true)
     */
    protected $rate;
    
    public function __construct() {
    
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return ProviderPayRate
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Provider
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @param Provider $provider
     * @return ProviderPayRate
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
        return $this;
    }

    /**
     * @return string
     */
    public function getNurseType()
    {
        return $this->nurse_type;
    }

    /**
     * @param string $nurse_type
     * @return ProviderPayRate
     */
    public function setNurseType($nurse_type)
    {
        $this->nurse_type = $nurse_type;
        return $this;
    }

    /**
     * @return string
     */
    public function getRateType()
    {
        return $this->rate_type;
    }

    /**
     * @param string $rate_type
     * @return ProviderPayRate
     */
    public function setRateType($rate_type)
    {
        $this->rate_type = $rate_type;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsCovid()
    {
        return $this->is_covid;
    }

    /**
     * @param bool $is_covid
     * @return ProviderPayRate
     */
    public function setIsCovid($is_covid)
    {
        $this->is_covid = $is_covid;
        return $this;
    }

    /**
     * @return float
     */
    public function getIncentive()
    {
        return $this->incentive;
    }

    /**
     * @param float $incentive
     * @return ProviderPayRate
     */
    public function setIncentive($incentive)
    {
        $this->incentive = $incentive;
        return $this;
    }

    /**
     * @return string
     */
    public function getPayOrBill()
    {
        return $this->pay_or_bill;
    }

    /**
     * @param string $pay_or_bill
     * @return ProviderPayRate
     */
    public function setPayOrBill($pay_or_bill)
    {
        $this->pay_or_bill = $pay_or_bill;
        return $this;
    }

    /**
     * @return float
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * @param float $rate
     * @return ProviderPayRate
     */
    public function setRate($rate)
    {
        $this->rate = $rate;
        return $this;
    }

    
    
}