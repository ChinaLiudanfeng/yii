<?php
$user = Yii::$app->user->identity->identity;
$mch_id = Yii::$app->user->identity->mch_id;

?>
<style>
    .app-manage .el-radio-button__inner {
        color: #444444;
        background: #EBEEF5;
    }
    .app-manage .manage-head {
        position: relative;
        height: 61px;
        line-height: 61px;
        text-align: center;
        color: #ffffff;
        border-radius: 8px 8px 0 0;
    }

    .app-manage .manage-head img {
        position: absolute;
        right: 0;
        bottom: 0;
    }

    .app-manage .manage-content {
        flex-wrap: wrap;
        border-radius: 0 0 8px 8px;
    }

    .app-manage .icon-down {
        width: 12px;
        height: 15px;
        background-image: url(statics/img/mall/statistic/icon_down.png);
        background-size: 100% 100%;
        background-repeat: no-repeat;
    }

    .app-manage .icon-up {
        width: 12px;
        height: 15px;
        background-image: url(statics/img/mall/statistic/icon_up.png);
        background-size: 100% 100%;
        background-repeat: no-repeat;
    }

    .app-manage .icon-equal {
        width: 16px;
        height: 4px;
        background-image: url(statics/img/mall/statistic/icon_equal.png);
        background-size: 100% 100%;
        background-repeat: no-repeat;
    }

    .app-manage .manage-box {
        font-size: 16px;
        flex-grow: 1;
        color: #333333;
        height: 150px;
        position: relative;
    }

    .app-manage .manage-box .title {
        padding-top: 15px;
        font-size: 30px;
        line-height: 1;
        color: #333333;
    }
    .app-manage .manage-box .refund-title {
        font-size: 12px;
        line-height: 1;
        color: #333333;
        position: absolute;
        bottom: 8px;
    }
    .app-manage .manage-box .compare {
        padding-top: 15px;
    }

    .app-manage .manage-box .compare span {
        color: #92959B;
        font-size: 16px;
        margin-right: 6px;
    }
</style>
<template id="app-manage">
    <div class="app-manage">
        <el-card shadow="never" style="margin-bottom: 10px">
            <div slot="header">
                <span>{{labelTitle}}</span>
                <app-new-export-dialog-2
                    style="float: right;margin-top: -5px"
                    v-if="showStatus !== 'operator'"
                    text='??????'
                    :params="searchData"
                    @selected="exportManage"
                    :directly=true
                    action_url="mall/data-statistics/all-num">
                </app-new-export-dialog-2>
            </div>
            <div style="margin-top:10px;margin-bottom: 30px">
                <el-radio-group v-model="manageSearch.timeStatus" size="small" @change="changeManageRadio">
                    <el-radio-button label="today">??????</el-radio-button>
                    <el-radio-button label="yesterday">??????</el-radio-button>
                    <el-radio-button label="one_week">7???</el-radio-button>
                    <el-radio-button label="all">??????</el-radio-button>
                </el-radio-group>
                <el-date-picker
                        @change="changeManagePicker"
                        style="margin-left: 15px"
                        size="small"
                        v-model="manageSearch.time"
                        type="daterange"
                        value-format="yyyy-MM-dd"
                        range-separator="???"
                        start-placeholder="????????????"
                        end-placeholder="????????????">
                </el-date-picker>
            </div>
            <!-- ?????? -->
            <div v-if="showStatus === `operator`">
                <div v-loading="manageLoading" flex="dir:left cross:center" style="flex-grow: 1;height: 150px;width: 100%">
                    <div class="manage-head user" style="background:#6AD497">
                        <div>???<br>???<br>???<br>???</div>
                        <img src="statics/img/mall/statistic/browse_icon.png" alt=""/>
                    </div>
                    <div flex="dir:left" class="manage-content" style="background-color: #f0fef6;width: 100%;height: 100%">
                        <div flex="dir:top cross:center main:center" class="manage-box" style="width: 50%">
                            <div flex="dir:left cross:center">
                                <div>??????????????????</div>
                            </div>
                            <div class="title" style="color:#409EFF;cursor: pointer"
                                 @click="$navigate({r:'mall/user/index'},true)"
                            >{{all_num.user_data.user_num}}
                            </div>
                            <div v-if="rankingTitle" flex="dir:left cross:center" class="compare">
                                <span>{{rankingTitle}}</span>
                                <div v-if="all_num.user_data.user_num_status === `up`" class="icon-up"></div>
                                <div v-if="all_num.user_data.user_num_status === `down`" class="icon-down"></div>
                                <div v-if="all_num.user_data.user_num_status === `equal`" class="icon-down"></div>
                            </div>
                        </div>
                        <!-- -->
                        <div flex="dir:top cross:center main:center" class="manage-box" style="width: 50%">
                            <div flex="dir:left cross:center">
                                <div>??????????????????</div>
                            </div>
                            <div class="title" v-text="formatNumText"></div>
                            <div v-if="rankingTitle" flex="dir:left cross:center" class="compare">
                                <span>{{rankingTitle}}</span>
                                <div v-if="all_num.user_data.data_num_status === `up`" class="icon-up"></div>
                                <div v-if="all_num.user_data.data_num_status === `down`" class="icon-down"></div>
                                <div v-if="all_num.user_data.data_num_status === `equal`" class="icon-down"></div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div v-else v-loading="manageLoading" flex="dir:left cross:center" style="width: 100%">
                <div :style="{'margin-right': showStatus === `store` ? `0px`: `27px`}" style="flex-grow:1">
                    <div class="manage-head" style="background:#409EFF">
                        <span>????????????</span>
                        <img style="width: 63px;height: 51px" src="statics/img/mall/statistic/payment_icon.png" alt=""/>
                    </div>
                    <div flex="dir:left" class="manage-content" style="background-color: #F6FAFF">
                        <!-- -->
                        <div flex="dir:top cross:center main:center" class="manage-box" style="width: 50%">
                            <div flex="dir:left cross:center">
                                <div>????????????????????????</div>
                                <el-tooltip class="item" effect="dark" content="???????????????????????????????????????" placement="top">
                                    <i class="el-icon-question"></i>
                                </el-tooltip>
                            </div>
                            <div class="title">{{all_num.order_data.order_num}}</div>
                            <div v-if="rankingTitle" flex="dir:left cross:center" class="compare">
                                <span>{{rankingTitle}}</span>
                                <div v-if="all_num.order_data.order_num_status === `up`" class="icon-up"></div>
                                <div v-if="all_num.order_data.order_num_status === `down`" class="icon-down"></div>
                                <div v-if="all_num.order_data.order_num_status === `equal`" class="icon-equal"></div>
                            </div>
                        </div>
                        <!-- -->
                        <div flex="dir:top cross:center main:center" class="manage-box" style="width: 50%">
                            <div flex="dir:left cross:center">
                                <div>?????????????????????</div>
                                <el-tooltip class="item" effect="dark" content="????????????????????????????????????" placement="top">
                                    <i class="el-icon-question"></i>
                                </el-tooltip>
                            </div>
                            <div class="title">{{all_num.order_data.user_num}}</div>
                            <div v-if="rankingTitle" flex="dir:left cross:center" class="compare">
                                <span>{{rankingTitle}}</span>
                                <div v-if="all_num.order_data.user_num_status === `up`" class="icon-up"></div>
                                <div v-if="all_num.order_data.user_num_status === `down`" class="icon-down"></div>
                                <div v-if="all_num.order_data.user_num_status === `equal`" class="icon-equal"></div>
                            </div>
                        </div>
                        <!-- -->
                        <div flex="dir:top cross:center main:center" class="manage-box" style="width: 50%">
                            <div flex="dir:left cross:center">
                                <div>?????????????????????</div>
                                <el-tooltip class="item" effect="dark" content="????????????????????????????????????" placement="top">
                                    <i class="el-icon-question"></i>
                                </el-tooltip>
                            </div>
                            <div class="title">{{all_num.order_data.total_pay_price}}</div>
                            <div v-if="rankingTitle" flex="dir:left cross:center" class="compare">
                                <span>{{rankingTitle}}</span>
                                <div v-if="all_num.order_data.total_pay_price_status === `up`" class="icon-up"></div>
                                <div v-if="all_num.order_data.total_pay_price_status === `down`"
                                     class="icon-down"></div>
                                <div v-if="all_num.order_data.total_pay_price_status === `equal`"
                                     class="icon-equal"></div>
                            </div>
                        </div>
                        <!-- -->
                        <div flex="dir:top cross:center main:center" class="manage-box" style="width: 50%">
                            <div flex="dir:left cross:center">
                                <div>????????????????????????</div>
                                <el-tooltip class="item" effect="dark" content="???????????????????????????????????????" placement="top">
                                    <i class="el-icon-question"></i>
                                </el-tooltip>
                            </div>
                            <div class="title">{{all_num.order_data.goods_num}}</div>
                            <div v-if="rankingTitle" flex="dir:left cross:center" class="compare">
                                <span>{{rankingTitle}}</span>
                                <div v-if="all_num.order_data.goods_num_status === `up`" class="icon-up"></div>
                                <div v-if="all_num.order_data.goods_num_status === `down`" class="icon-down"></div>
                                <div v-if="all_num.order_data.goods_num_status === `equal`" class="icon-equal"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="showStatus === `super-account` || showStatus === `sub-account`" style="flex-grow:1;width:300px">
                    <div class="manage-head" style="background:#6AD497">
                        <span>????????????</span>
                        <img src="statics/img/mall/statistic/browse_icon.png" alt=""/>
                    </div>
                    <div flex="dir:left" class="manage-content" style="background-color: #F0FEF6">
                        <!-- -->
                        <div flex="dir:top cross:center main:center" class="manage-box" style="width: 100%">
                            <div flex="dir:left cross:center">
                                <div>??????????????????</div>
                            </div>
                            <div class="title"
                                 style="color:#409EFF;cursor: pointer"
                                 @click="$navigate({r:'mall/user/index'},true)">
                                {{all_num.user_data.user_num}}
                            </div>
                            <div v-if="rankingTitle" flex="dir:left cross:center" class="compare">
                                <span>{{rankingTitle}}</span>
                                <div v-if="all_num.user_data.user_num_status === `up`" class="icon-up"></div>
                                <div v-if="all_num.user_data.user_num_status === `down`" class="icon-down"></div>
                                <div v-if="all_num.user_data.user_num_status === `equal`" class="icon-equal"></div>
                            </div>
                        </div>
                        <!-- -->
                        <div flex="dir:top cross:center main:center" class="manage-box" style="width: 100%">
                            <div flex="dir:left cross:center">
                                <div>??????????????????</div>
                            </div>
                            <div class="title" v-text="formatNumText"></div>
                            <div v-if="rankingTitle" flex="dir:left cross:center" class="compare">
                                <span>{{rankingTitle}}</span>
                                <div v-if="all_num.user_data.data_num_status === `up`" class="icon-up"></div>
                                <div v-if="all_num.user_data.data_num_status === `down`" class="icon-down"></div>
                                <div v-if="all_num.user_data.data_num_status === `equal`" class="icon-equal"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="showStatus === `super-account` || showStatus === `mch` || showStatus === `sub-account`"
                     style="flex-grow:1;margin-left: 27px">
                    <div class="manage-head" style="background:#FC9F4C">
                        <span>????????????</span>
                        <img style="width: 63px;height: 51px" src="statics/img/mall/statistic/profit_icon.png" alt=""/>
                    </div>
                    <div flex="dir:left" class="manage-content" style="background-color: #FFF7E9">
                        <!-- -->
                        <div flex="dir:top cross:center main:center" class="manage-box" style="width: 50%">
                            <div flex="dir:left cross:center">
                                <div>?????????????????????</div>
                                <el-tooltip class="item" effect="dark" content="??????????????????????????????????????????????????????" placement="top">
                                    <i class="el-icon-question"></i>
                                </el-tooltip>
                            </div>
                            <div class="title">{{all_num.pay_data[3].amount}}</div>
                            <div v-if="rankingTitle" flex="dir:left cross:center" class="compare">
                                <span>{{rankingTitle}}</span>
                                <div v-if="all_num.pay_data[3].amount_status === `up`" class="icon-up"></div>
                                <div v-if="all_num.pay_data[3].amount_status === `down`" class="icon-down"></div>
                                <div v-if="all_num.pay_data[3].amount_status === `equal`" class="icon-equal"></div>
                            </div>
                            <div v-if="all_num.pay_data[3].refund > 0" class="refund-title">?????????
                                ???{{all_num.pay_data[3].refund}}
                            </div>
                        </div>
                        <!-- -->
                        <div flex="dir:top cross:center main:center" class="manage-box" style="width: 50%">
                            <div flex="dir:left cross:center">
                                <div>?????????????????????</div>
                                <el-tooltip class="item" effect="dark" content="??????????????????????????????????????????????????????" placement="top">
                                    <i class="el-icon-question"></i>
                                </el-tooltip>
                            </div>
                            <div class="title">{{all_num.pay_data[1].amount}}</div>
                            <div v-if="rankingTitle" flex="dir:left cross:center" class="compare">
                                <span>{{rankingTitle}}</span>
                                <div v-if="all_num.pay_data[1].amount_status === `up`" class="icon-up"></div>
                                <div v-if="all_num.pay_data[1].amount_status === `down`" class="icon-down"></div>
                                <div v-if="all_num.pay_data[1].amount_status === `equal`" class="icon-equal"></div>
                            </div>
                            <div v-if="all_num.pay_data[1].refund > 0" class="refund-title">?????????
                                ???{{all_num.pay_data[1].refund}}
                            </div>
                        </div>
                        <!-- -->
                        <div flex="dir:top cross:center main:center" class="manage-box" style="width: 50%">
                            <div flex="dir:left cross:center">
                                <div>????????????????????????</div>
                                <el-tooltip class="item" effect="dark" content="??????????????????????????????????????????????????????" placement="top">
                                    <i class="el-icon-question"></i>
                                </el-tooltip>
                            </div>
                            <div class="title">{{all_num.pay_data[4].amount}}</div>
                            <div v-if="rankingTitle" flex="dir:left cross:center" class="compare">
                                <span>{{rankingTitle}}</span>
                                <div v-if="all_num.pay_data[4].amount_status === `up`" class="icon-up"></div>
                                <div v-if="all_num.pay_data[4].amount_status === `down`" class="icon-down"></div>
                                <div v-if="all_num.pay_data[4].amount_status === `equal`" class="icon-equal"></div>
                            </div>
                            <div v-if="all_num.pay_data[4].refund > 0" class="refund-title">?????????
                                ???{{all_num.pay_data[4].refund}}
                            </div>
                        </div>
                        <!-- -->
                        <div flex="dir:top cross:center main:center" class="manage-box" style="width: 50%">
                            <div flex="dir:left cross:center">
                                <div>???????????????????????????</div>
                                <el-tooltip class="item" effect="dark" content="??????????????????????????????????????????????????????" placement="top">
                                    <i class="el-icon-question"></i>
                                </el-tooltip>
                            </div>
                            <div class="title">{{all_num.pay_data[2].amount}}</div>
                            <div v-if="rankingTitle" flex="dir:left cross:center" class="compare">
                                <span>{{rankingTitle}}</span>
                                <div v-if="all_num.pay_data[2].amount_status === `up`" class="icon-up"></div>
                                <div v-if="all_num.pay_data[2].amount_status === `down`" class="icon-down"></div>
                                <div v-if="all_num.pay_data[2].amount_status === `equal`" class="icon-equal"></div>
                            </div>
                            <div v-if="all_num.pay_data[2].refund > 0" class="refund-title">?????????
                                ???{{all_num.pay_data[2].refund}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </el-card>
    </div>
</template>
<style>
    /*************/
    .manage-head.user {
        height: 150px;
        width: 80px;
        background: #6AD497;
        border-radius: 8px 0 0 8px;
    }

    .manage-head.user > div {
        line-height: 1.2;
        margin-top: 33px;
    }

    .manage-head.user > img {
        width: 63px;
        height: 51px;
    }
</style>
<script>
    Vue.component('app-manage', {
        template: '#app-manage',
        props: {
            storeId: String,
        },
        data() {
            return {
                manageSearch: {
                    store_id: '',
                    time: null,
                    date_start: '',
                    date_end: '',
                    timeStatus: 'today',
                },
                searchData: {
                    type: []
                },
                manageLoading: false,
                timeStr: {
                    'today': '??????',
                    'yesterday': '??????',
                    'one_week': '7???',
                    'all': '??????',
                },
                all_num: {
                    user_data: {},
                    order_data: {},
                    order_num: {},
                    pay_data: {
                        1: {},
                        2: {},
                        3: {},
                        4: {},
                    },
                },
            }
        },
        watch: {
            'manageSearch.time'(newData, oldData) {
                if (newData && newData.length >= 2) {
                    this.manageSearch.date_start = newData[0];
                    this.manageSearch.date_end = newData[1];
                } else {
                    this.manageSearch.date_start = '';
                    this.manageSearch.date_end = '';
                }
            },
            'storeId'(newData, oldData) {
                this.manageSearch.store_id = newData;
                this.getData();
            },
        },
        computed: {
            formatNumText() {
                if (this.all_num.user_data.data_num) {
                    let numberFormat = function (value) {
                        let param = {};
                        let k = 10000,
                            sizes = ['', '???', '???', '??????'],
                            i;
                        if (value < k) {
                            param.value = value
                            param.unit = ''
                        } else {
                            i = Math.floor(Math.log(value) / Math.log(k));
                            param.value = Math.floor(((value / Math.pow(k, i))) * 100) / 100;
                            param.unit = sizes[i];
                        }
                        return param;
                    }
                    let num = this.all_num.user_data.data_num;
                    let format = numberFormat(num);
                    return format.value + format.unit;
                }
            },
            showStatus() {
                if (this.storeId !== undefined) {
                    return 'store';
                }
                if ("<?= $mch_id ?>" > 0) {
                    return 'mch';
                }
                if ("<?= $user['is_admin'] ?>" == 1) {
                    return 'sub-account';
                }
                if ("<?= $user['is_super_admin'] ?>" == 1) {
                    return 'super-account';
                }
                if ("<?= $user['is_operator'] ?>" == 1) {
                    return 'operator';
                }
            },
            labelTitle() {
                switch (this.showStatus) {
                    case 'operator':
                        return '????????????';
                    default:
                        return '????????????';
                }
            },
            rankingTitle() {
                let timeStatus = this.manageSearch.timeStatus;
                let timeStr = {
                    '': '',
                    today: '?????????',
                    yesterday: '?????????',
                    one_week: '????????????',
                    all: '',
                };
                return timeStr[timeStatus];
            },
            tableForm() {
                if (this.tableSearch.type === `1`) {
                    return this.payForm;
                }
                if (this.tableSearch.type === `2`) {
                    return this.dataForm;
                }
            }
        },
        mounted() {
            let day = this.manageSearch.timeStatus;
            this.manageSearch.time = this.formatTime(day);
            if (this.storeId === undefined) {
                this.getData();
            }
        },

        methods: {
            exportManage() {
                let type = [];
                switch (this.showStatus) {
                    case 'store':
                        type.push('order_data');
                        break;
                    case 'mch':
                        type.push('order_data', 'pay_data');
                        break;
                    case 'sub-account':
                        type.push('order_data', 'user_data', 'pay_data');
                        break;
                    case 'super-account':
                        type.push('order_data', 'user_data', 'pay_data');
                        break;
                }

                this.searchData.type = JSON.stringify(type);
            },
            formatTime(limit) {
                let time = ",";
                if (limit === 'today') {
                    time = "<?php
                        $date = new DateTime();
                        $currentTime = $date->format('Y-m-d');
                        echo join(',', [$currentTime, $currentTime]);
                        ?>";
                }
                if (limit === 'yesterday') {
                    time = "<?php
                        $date = new DateTime();
                        $currentTime = $date->format('Y-m-d');
                        $interval = new DateInterval('P1D');
                        $date->sub($interval);
                        echo join(',', [$date->format('Y-m-d'), $date->format('Y-m-d')]);
                        ?>";
                }
                if (limit === 'one_week') {
                    time = "<?php
                        $date = new DateTime();
                        $currentTime = $date->format('Y-m-d');

                        $interval = new DateInterval('P6D');
                        $date->sub($interval);
                        echo join(',', [$date->format('Y-m-d'), $currentTime]);
                        ?>";
                }
                return time.split(',');
            },
            changeManagePicker(row) {
                if (row) {
                    this.manageSearch.timeStatus = '';
                } else {
                    this.manageSearch.timeStatus = 'all';
                }
                this.getData();
            },
            changeManageRadio(row) {
                this.manageSearch.time = this.formatTime(row);
                this.getData();
            },
            getData() {
                setTimeout(() => {
                    let params = Object.assign({r: `mall/data-statistics/all-num`}, this.manageSearch);
                    this.manageLoading = true;
                    request({
                        params,
                    }).then(e => {
                        this.manageLoading = false;
                        if (e.data.code === 0) {
                            this.all_num = e.data.data;
                        }
                    }).catch(e => {
                        this.manageLoading = false;
                    })
                 });
            },
        }
    })
</script>