<template>
    <div class="frame rounded">
        <ul class="nav module-menu" role="tablist">
            <li class="nav-item">
                <a class="menu-item d-flex align-items-center active" id="all-activity-tab"
                    data-toggle="tab" href="#all-activity" role="tab" aria-controls="all-activity"
                    aria-selected="true">
                    <span>All</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="menu-item d-flex align-items-center" id="all-calllog-tab" data-toggle="tab"
                    href="#all-calllog" role="tab" aria-controls="all-calllog" aria-selected="false">
                    <span>Call Log</span>
                    <div class="badge gray ml-2">{{ length['Call Log'] }}</div>
                </a>
            </li>
            <li class="nav-item">
                <a class="menu-item d-flex align-items-center" id="all-so-tab" data-toggle="tab"
                    href="#all-so" role="tab" aria-controls="all-so" aria-selected="false">
                    <span>Create SO</span>
                    <div class="badge gray ml-2">{{ length['Sales Order'] }}</div>
                </a>
            </li>
            <li class="nav-item">
                <a class="menu-item d-flex align-items-center" id="all-payment-tab" data-toggle="tab"
                    href="#all-payment" role="tab" aria-controls="all-payment" aria-selected="false">
                    <span>Payment</span>
                    <div class="badge gray ml-2">{{ length['Payment'] }}</div>
                </a>
            </li>
            <li class="nav-item">
                <a class="menu-item d-flex align-items-center" id="all-notes-tab" data-toggle="tab"
                    href="#all-notes" role="tab" aria-controls="all-notes" aria-selected="false">
                    <span>Notes</span>
                    <div class="badge gray ml-2">{{ length['Note'] }}</div>
                </a>
            </li>
        </ul>

        <div class="tab-content outlet-activity">
            <!-- ALL ACTIVITY -->
            <div class="tab-pane fade show active" id="all-activity" role="tabpanel"
                aria-labelledby="all-activity-tab">
                <activity-info :data="data">
                </activity-info>
            </div>

            <!-- ALL CALL LOG -->
            <div class="tab-pane fade" id="all-calllog" role="tabpanel" aria-labelledby="all-calllog-tab">
                <activity-info :data="getActivitiesByType('Call Log')">
                </activity-info>
            </div>

            <!-- ALL SO -->
            <div class="tab-pane fade" id="all-so" role="tabpanel" aria-labelledby="all-so-tab">
                <activity-info :data="getActivitiesByType('Sales Order')">
                </activity-info>
            </div>

            <!-- ALL PAYMENT -->
            <div class="tab-pane fade" id="all-payment" role="tabpanel" aria-labelledby="all-payment-tab">
                <activity-info :data="getActivitiesByType('Payment')">
                </activity-info>
            </div>

            <!-- ALL NOTES -->
            <div class="tab-pane fade" id="all-notes" role="tabpanel" aria-labelledby="all-notes-tab">
                <activity-info :data="getActivitiesByType('Note')">
                </activity-info>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import moment from 'moment';
import ActivityInfo from './activity-info';

export default {
    components: {
        'activity-info': ActivityInfo
    },

    props: [
        'customer-id',
        'users'
    ],

    data: function () {
        return {
            data: {},
            length: {
                'Call Log': 0,
                'Sales Order': 0,
                'Payment': 0,
                'Notes': 0
            },
            usersDict: this.makeUsersDict(this.users)
        };
    },

    mounted() {
        var self = this;

        axios.get(`${this.$root.url}/journey-planner/outlet-management/${this.customerId}/activities`).then(response => {
            this.data = response.data.data;
        });
    },

    methods: {
        getActivitiesByType(type) {
            var content = Object.assign({}, this.data);

            Object.keys(content).forEach(key => {
                content[key] = content[key].filter(activity => {
                    return activity.type == type;
                })

                if (content[key].length == 0) {
                    delete content[key];
                }
            })

            return content;
        },

        makeUsersDict() {
            var usersDict = {};

            this.users.forEach(item => {
                usersDict[item._base.id] = item;
            })

            return usersDict;
        },

        calculateActivityTypeLength(data) {
            this.length['Call Log'] = 0;
            this.length['Sales Order'] = 0;
            this.length['Payment'] = 0
            this.length['Note'] = 0;

            Array.prototype.concat.apply([], Object.values(data)).forEach(activity => {
                switch (activity.type) {
                    case 'Call Log':
                        this.length['Call Log']++;
                        return;
                    case 'Sales Order':
                        this.length['Sales Order']++;
                        return;
                    case 'Payment':
                        this.length['Payment']++;
                        return;
                    case 'Note':
                        this.length['Note']++;
                        return;
                }
            })
        }
    },

    watch: {
        data() {
            Object.keys(this.data).forEach(key => {
                this.data[key].sort(function compare(a, b) {
                    return moment(b.time, 'hh:mm a') - moment(a.time, 'hh:mm a');
                })
            })

            this.calculateActivityTypeLength(this.data);
        }
    }
};
</script>
