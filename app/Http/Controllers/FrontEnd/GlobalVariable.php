<?php

namespace App\Http\Controllers\FrontEnd;

class GlobalVariable
{
    public $module;
    public $subModule;
    public $menu;
    public $menuUrl;
    public $menuRoute;
    public $menuParam;

    public $actionGetMenu;

    // MASTER (PPM)
    public $actionGetPriceList;
    public $actionGetPack;
    public $actionGetUnit;
    public $actionGetItem;
    public $actionGetType;
    public $actionGetGroup;
    public $actionGetCategory;
    public $actionGetAccount;
    public $actionGetSettingAccount;
    public $actionGetBranch;
    public $actionGetRegion;
    public $actionGetProvince;
    public $actionGetBank;
    public $actionGetLocation;
    public $actionGetWarehouse;
    public $actionGetCityDistrict;
    public $actionGetSubdistrict;

    public $actionGetInitialAccountBalance;
    public $actionGetStockBalance;
    public $actionGetPayableMinus;
    public $actionGetPayablePlus;
    public $actionGetReceivablesMinus;
    public $actionGetReceivablesPlus;
    public $actionGetMainIncomingGiro;
    public $actionGetMainOutgoingGiro;
    public $actionGetOtherIncomingGiro;
    public $actionGetMainGiroBounce;

    public $actionGetColor;
    public $actionGetCategorySize;
    public $actionGetBomEvaMaterial;
    public $actionGetBomEvaMaterialPigment;

    public $actionGetCustomer;
    public $actionGetSupplier;
    public $actionGetExpedition;
    public $actionGetCustomerCategory;
    public $actionGetCustomerGroup;
    public $actionGetDepartment;
    public $actionGetPosition;
    public $actionGetDivision;
    public $actionGetEmployee;

    // Transaction (PPM)
    // Purchase Request
    public $actionGetPurchaseRequest;
    public $actionGetServicePurchaseRequest;
    // Sale
    public $actionGetMarketingOrder;
    public $actionGetMarketingOrderClosing;
    public $actionGetSalesOrder;
    public $actionGetSalesOrderClosing;
    public $actionGetSalesReturn;
    // Warehouse
    public $actionGetStockTaking;

    public $actionGetCurrency;
    public $actionGetCity;
    public $actionGetBranchOffice;
    public $actionGetCountry;
    public $actionGetNote;
    public $actionBc;

    public $actionGetPrincipalCommodity;
    public $actionGetPrincipalBankAccount;
    public $actionGetPrincipalBlacklist;
    public $actionGetPrincipalCategory;
    public $actionGetPrincipalCommodityCategory;
    public $actionGetPrincipalData;
    public $actionGetPrincipalGroup;
    public $actionGetPrincipalTitle;

    public $actionGetDocumentSetting;

    public $actionGetPort;
    public $actionGetPortInterchange;
    public $actionGetMeasurementUnit;
    public $actionGetCarrier;

    public $actionGetLogo;
    public $actionGetNationalHoliday;

    public $actionGetContainerAgencies;
    public $actionGetContainer;
    public $actionGetContainerSize;
    public $actionGetContainerType;

    public $actionGetServiceBudget;
    public $actionGetService;
    public $actionGetServiceGroup;
    public $actionGetServicePrice;
    public $actionGetServiceTag;
    public $actionGetServiceTerm;
    public $actionGetServiceCategory;
    public $actionGetServiceFromSupplier;

    public $actionGetRemark;
    public $actionGetWebsite;
    public $actionGetOriginalCopy;
    public $actionGetPaymentMethod;
    public $actionGetPaymentTerm;

    public $actionGetUser;
    public $actionUpdateUser;
    public $actionGetUserGroup;
    public $actionGetReminder;
    public $actionGetUserTeam;
    public $actionGetLogActivity;

    public $actionGetQuotation;

    public $actionGetPreOrder;
    public $actionGetNotification;

    public $actionGetRfq;

    // Enum
    public $actionGetEnumNoteCategoryData;
    public $actionGetEnumBcsOrderTypeData;
    public $actionGetEnumUserGroupData;
    public $actionGetEnumContainerTypeCategoryData;
    public $actionGetEnumServiceTermCategoryData;
    public $actionGetEnumPrincipalTitles;
    public $actionGetEnumPrincipalsRole;
    public $actionGetEnumUserTeamOnhandStatus;
    public $actionGetEnumRfqTrade;
    public $actionGetEnumRfqShippingMode;
    public $actionGetEnumRfqTypeOfShipment;

    // Grid data
    public $actionGetPrincipalCommodityGrid;
    public $actionGetPrincipalCategoryGrid;
    public $actionGetPrincipalPicGrid;
    public $actionGetPrincipalAddressGrid;

    public $actionGetUserTeamUserGrid;

    public $actionGetCargoGrid;
    public $actionGetServiceBuyGrid;
    public $actionGetServiceSellGrid;

    public function __construct()
    {
        // master-data
        $this->actionGetMenu = 'getMenu';
        $this->actionGetBranchOffice = 'getBranch';
        $this->actionGetCity = 'getCity';
        $this->actionGetItem = 'getItem';
        $this->actionGetCountry = 'getCountry';
        $this->actionGetCurrency = 'getCurrency';
        $this->actionGetNote = 'getNote';
        $this->actionGetUnit = 'getUnit';
        $this->actionGetPack = 'getPack';
        $this->actionGetPriceList = 'getPriceList';
        $this->actionGetCategory = 'getCategory';
        $this->actionGetGroup = 'getGroup';
        $this->actionGetType = 'getType';
        $this->actionGetColorFactor = 'getColorFactor';
        $this->actionGetDocumentSetting = 'getDocumentSetting';
        $this->actionGetPrincipalCommodity = 'getPrincipalCommodity';
        $this->actionGetPrincipalBankAccount = 'getPrincipalBankAccount';
        $this->actionGetPrincipalAddress = 'getPrincipalAddress';
        $this->actionGetPrincipalPic = 'getPrincipalPic';
        $this->actionGetLockedParty = 'getLockedParty';
        $this->actionGetPrincipalCategory = 'getPrincipalCategory';
        $this->actionGetPrincipalCommodityCategory = 'getCommodityCategory';
        $this->actionGetPrincipalData = 'getPrincipal';
        $this->actionGetPrincipalGroup = 'getPrincipalGroup';
        $this->actionGetPrincipalTitle = 'getPrincipalTitle';
        $this->actionGetNotification = 'getNotification';
        $this->actionGetPort = 'getPort';
        $this->actionGetPortInterchange = 'getPortInterchange';
        $this->actionGetMeasurementUnit = 'getMeasurementUnit';
        $this->actionGetCarrier = 'getCarrier';
        $this->actionGetLogo = 'getLogo';
        $this->actionGetNationalHoliday = 'getNationalHoliday';
        $this->actionGetContainerAgencies = 'getContainerAgency';
        $this->actionGetContainer = 'getContainer';
        $this->actionGetContainerSize = 'getContainerSize';
        $this->actionGetContainerType = 'getContainerType';
        $this->actionGetServiceBudget = 'getServiceBudget';
        $this->actionGetService = 'getService';
        $this->actionGetServiceGroup = 'getServiceGroup';
        $this->actionGetServicePrice = 'getServicePrice';
        $this->actionGetServiceTag = 'getServiceTag';
        $this->actionGetServiceTerm = 'getServiceTerm';
        $this->actionGetServiceCategory = 'getServiceCategory';
        $this->actionGetRemark = 'getRemark';
        $this->actionGetWebsite = 'getWebsite';
        $this->actionGetOriginalCopy = 'getOriginalCopy';
        $this->actionGetPaymentMethod = 'getPaymentMethod';
        $this->actionGetPaymentTerm = 'getPaymentTerm';
        $this->actionGetDeliveryStatus = 'getDeliveryStatus';
        $this->actionGetTaxInvoice = 'getTaxInvoice';
        $this->actionGetServiceFromSupplier = 'getServiceFromSupplier';
        $this->actionGetCustomerCategory = 'getCustomerCategory';
        $this->actionGetCustomerGroup = 'getCustomerGroup';
        $this->actionGetDepartment = 'getDepartment';
        $this->actionGetPosition = 'getPosition';
        $this->actionGetDivision = 'getDivision';
        $this->actionGetEmployee = 'getEmployee';

        // master-data (PPM)
        $this->actionGetAccount = 'getAccount';
        $this->actionGetSettingAccount = 'getSettingAccount';
        $this->actionGetBranch = 'getBranch';
        $this->actionGetRegion = 'getRegion';
        $this->actionGetProvince = 'getProvince';
        $this->actionGetBank = 'getBank';
        $this->actionGetLocation = 'getLocation';
        $this->actionGetWarehouse = 'getWarehouse';
        $this->actionGetCityDistrict = 'getCityDistrict';
        $this->actionGetSubdistrict = 'getSubdistrict';
        $this->actionGetInitialAccountBalance = 'getInitialAccountBalance';
        $this->actionGetStockBalance = 'getStockBalance';
        $this->actionGetPayableMinus = 'getPayableMinus';
        $this->actionGetPayablePlus = 'getPayablePlus';
        $this->actionGetReceivablesMinus = 'getReceivablesMinus';
        $this->actionGetReceivablesPlus = 'getReceivablesPlus';
        $this->actionGetMainIncomingGiro = 'getMainIncomingGiro';
        $this->actionGetMainOutgoingGiro = 'getMainOutgoingGiro';
        $this->actionGetOtherIncomingGiro = 'getOtherIncomingGiro';
        $this->actionGetMainGiroBounce = 'getMainGiroBounce';
        $this->actionGetCustomer = 'getCustomer';
        $this->actionGetSupplier = 'getSupplier';
        $this->actionGetExpedition = 'getExpedition';

        $this->actionGetColor = 'getColor';
        $this->actionGetCategorySize = 'getCategorySize';
        $this->actionGetBomEvaMaterial = 'getBomEvaMaterial';
        $this->actionGetBomEvaMaterialPigment = 'getBomEvaMaterialPigment';

        // Transaction (PPM)
        $this->actionGetPurchaseRequest = 'getPurchaseRequest';
        $this->actionGetServicePurchaseRequest = 'getServicePurchaseRequest';
        // Sale
        $this->actionGetMarketingOrder = 'getMarketingOrder';
        $this->actionGetMarketingOrderClosing = 'getMarketingOrderClosing';
        $this->actionGetSalesOrder = 'getSalesOrder';
        $this->actionGetSalesOrderClosing = 'getSalesOrderClosing';
        $this->actionGetSalesReturn = 'getSalesReturn';
        // Warehouse
        $this->actionGetStockTaking = 'getStockTaking';

        // setting
        $this->actionGetUser = 'getUser';
        $this->actionUpdateUser = 'updateUser';
        $this->actionGetReminder = 'getReminder';
        $this->actionGetUserTeam = 'getUserTeam';
        $this->actionGetUserGroup = 'getUserGroup';
        $this->actionGetLogActivity = 'getLogActivity';

        // prebooking
        $this->actionGetQuotation = 'getQuotation';

        // preorder
        $this->actionGetPreOrder = 'getPreOrder';
        $this->actionGetRfq = 'getRfq';

        // enum
        $this->actionGetEnumNoteCategoryData = 'getEnumNoteCategoryData';
        $this->actionGetEnumBcsOrderTypeData = 'getEnumBcsOrderTypeData';
        $this->actionGetEnumContainerTypeCategoryData = 'getEnumContainerTypeCategoryData';
        $this->actionGetEnumPrincipalTitles = 'getEnumPrincipalTitleData';
        $this->actionGetEnumPrincipalsRole = 'getEnumPrincipalRoleData';
        $this->actionGetEnumUserTeamOnhandStatus = 'getEnumUserTeamOnhandStatusData';
        $this->actionGetEnumRfqTrade = 'getEnumRfqTradeData';
        $this->actionGetEnumRfqShippingMode = 'getEnumRfqShippindModeData';
        $this->actionGetEnumRfqTypeOfShipment = 'getEnumRfqTypeOfShipmentData';
        $this->actionGetEnumUserGroupData = 'getEnumUserGroupData';
        $this->actionGetEnumServiceTermCategoryData = 'getEnumServiceTermCategoryData';

        // grid
        $this->actionGetCargoGrid = 'getRfqCargoGrid';
        $this->actionGetServiceBuyGrid = 'getRfqServiceBuyGrid';
        $this->actionGetServiceSellGrid = 'getRfqServiceSellGrid';
        $this->actionGetUserTeamUserGrid = 'getUserTeamUserGrid';
        $this->actionGetPrincipalCommodityGrid = 'getPrincipalCommodityGrid';
        $this->actionGetPrincipalCategoryGrid = 'getPrincipalCategoryGrid';
        $this->actionGetPrincipalPicGrid = 'getPrincipalPicGrid';
        $this->actionGetPrincipalAddressGrid = 'getPrincipalAddressGrid';
    }

    public function ModuleGlobal($module, $subModule, $menuUrl, $menuRoute, $menuParam)
    {
        // Initialize your global properties in the constructor
        $this->module = $module;
        $this->subModule = $subModule;
        $this->menuUrl = $menuUrl;
        $this->menuRoute = $menuRoute;
        $this->menuParam = $menuParam;
    }
}
