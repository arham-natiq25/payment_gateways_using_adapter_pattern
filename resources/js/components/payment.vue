<template>
    <button class="btn btn-primary" @click="unhideSetting">Show Setting</button>
    <button class="btn btn-danger" @click="hideSetting">Hide Setting</button>
    <div v-if="showsetting">
        <div class="row">
            <div class="col-7">
                <h3>Select Payment Gateway</h3>
                <input type="radio" name="gateway" v-model="gateway" id="" :value="1" />
                Stripe
                <input type="radio" name="gateway" v-model="gateway" id="" :value="0" />
                Authorize.net

                <button class="btn btn-warning" @click="setGateway">Update</button>
            </div>
        </div>
    </div>

    <div v-show="active===1">
        <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>
        <div v-if="successMessage" class="alert alert-success">
            {{ successMessage }}
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <b>
                        <label for="card-name">Cardholder's Name</label>
                    </b>
                    <input type="text" id="card-name" v-model="cardholderName" class="form-control" />
                    <br />
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-6">
                <div class="form-group">
                    <b>
                        <label for="email">Email</label>
                    </b>
                    <input type="email" id="email" v-model="email" class="form-control" /><br />
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <b>
                        <label for="card-number">Card Number</label>
                    </b>
                    <div id="card-number" class="form-control card-input"></div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <b>
                    <label for="card-expiry">Expiration Date</label>
                </b>
                <div id="card-expiry" class="form-control card-input"></div>
            </div>
            <div class="col-md-3">
                <b>
                    <label for="card-cvc">CVC</label>
                </b>
                <div id="card-cvc" class="form-control card-input"></div>
            </div>
        </div>
        <button class="btn btn-success mt-3" @click="submit">Submit</button>
    </div>
    <!-- ON CLICK OF SUBMIT SOME TASKS PERFORMS -->



    <div v-show="active===0">
        <div class="col-md-6">
            <h3>Authorize Gateway</h3>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <b>
                            <label for="card-name">Cardholder's Name</label>
                        </b>
                        <input type="text" id="card-name" v-model="cardholderName" class="form-control" />
                        <br />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="form-group">
                        <b>
                            <label for="email">Email</label>
                        </b>
                        <input type="email" id="email" v-model="email" class="form-control" /><br />
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label for="cardNumber" class="form-label">Card Number</label>
                <input type="text" class="form-control" id="cardNumber" maxlength="16" minlength="16" v-model="cardNumber"
                    placeholder="Enter card number" required />
            </div>

            <div class="mb-3">
                <label for="cvv" class="form-label">CVV</label>
                <input type="text" class="form-control" id="cvv" maxlength="4s" minlength="3" v-model="cvv"
                    placeholder="Enter CVV" required />
            </div>

            <div class="mb-3">
                <label for="expiryMonth" class="form-label">Expiry Month</label>
                <select class="form-select" id="expiryMonth" v-model="expiryMonth" required>
                    <option value="" disabled>Select month</option>
                    <option v-for="month in months" :key="month" :value="month">
                        {{ month }}
                    </option>
                </select>
            </div>

            <div class="mb-3">
                <label for="expiryYear" class="form-label">Expiry Year</label>
                <select class="form-select" id="expiryYear" v-model="expiryYear" required>
                    <option value="" disabled>Select year</option>
                    <option v-for="year in dynamicYears" :key="year" :value="year">
                        {{ year }}
                    </option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" @click="submitFormAuthroize">
                Submit
            </button>
        </div>
    </div>
<!-- // card payment -->
    <div>
        <div class="row">
            <div class="col-md-8">
                <b><label>Select Card</label></b>

                <div v-for="card in cards" :key="card.id">
                    <input type="radio" :id="'card_' + card.id" name="selectedCard" class="m-2" :value="card"
                        v-model="selectedCard" />
                    <label :for="'card_' + card.id" class="m-2">
                        **** **** **** {{ card.last_four_digit }}
                    </label>
                </div>
            </div>

            <div class="col-md-3 mt-4">
                <button class="btn btn-success" @click="payWithCard">Pay</button>
            </div>
        </div>
    </div>
</template>
<script>
import axios from "axios";
export default {
    name: "payment",
    data() {
        return {
            gateway: null,
            showsetting: false,
            elements: null,
            stripe: null,
            cardNumberElement: null,
            cardExpiryElement: null,
            cardCvcElement: null,
            payment: 100,
            errorMessage: "",
            successMessage: "",
            cardholderName: "",
            email: "",
            user_id: null,
            cards: [],
            selectedCard: null,
            months: [
                "01",
                "02",
                "03",
                "04",
                "05",
                "06",
                "07",
                "08",
                "09",
                "10",
                "11",
                "12",
            ],
            years: ["2023", "2024", "2025", "2026", "2027", "2028", "2029", "2030"],
            cardNumber: "",
            cvv: "",
            expiryMonth: "",
            expiryYear: "",
            active:{}
        };
    },
    mounted() {
        this.getGateway();
        this.getCustomerCards();
        this.loadStripe();

    },
    computed: {
        dynamicYears() {
            const currentYear = new Date().getFullYear();
            const futureYears = Array.from(
                { length: 15 },
                (_, index) => currentYear + index
            );
            return futureYears.map(String); // Convert years to strings
        },
    },
    methods: {
        setGateway() {
            if (this.gateway != null) {
                const value = { gateway: this.gateway };
                axios.post("/gateway", value);
                window.location.href = window.location.href
            }
        },
        getGateway(){
         axios.get('/get/gateway').then((res)=>{
            this.active=res.data
         })
        },
        unhideSetting() {
            this.showsetting = true;
        },
        hideSetting() {
            this.showsetting = false;
        },

        loadStripe() {
            // if current windonw has Stripe set primary key
            if (window.Stripe) {
                this.stripe = window.Stripe(
                    "pk_test_51NUU03Emu0Ala7lxKFLz0kgK8mfOVQr99wlJMIDW39xzneQ0B6Zb2x9irWjjNuldkUYyDFQG11FE50M6px3wvrVx00A0milkpo"
                );
                this.elements = this.stripe.elements();
                // Create an instance of the card number Element
                this.cardNumberElement = this.elements.create("cardNumber", {
                    placeholder: "Card Number",
                });
                this.cardNumberElement.mount("#card-number");

                // Create an instance of the card expiry Element
                this.cardExpiryElement = this.elements.create("cardExpiry", {
                    placeholder: "MM / YY",
                });
                this.cardExpiryElement.mount("#card-expiry");

                // Create an instance of the card cvc Element
                this.cardCvcElement = this.elements.create("cardCvc", {
                    placeholder: "CVC",
                });
                this.cardCvcElement.mount("#card-cvc");
            } else {
                // Handle the case when Stripe is not available
                console.error("Stripe is not available");
            }
        },
        async submit() {
            this.errorMessage = "";
            const cardElement = this.elements.getElement("cardNumber");
            const { paymentMethod, error } = await this.stripe.createPaymentMethod({
                type: "card",
                card: cardElement,
                billing_details: {
                    name: this.cardholderName,
                    email: this.email,
                },
            });
            console.log("Error from Stripe:", error); // Log Stripe error
            if (error) {
                this.errorMessage = error.message;
            } else {
                this.processPayment(paymentMethod.id);
            }
            console.log("Final error message:", this.errorMessage); // Log final error message
        },
        async processPayment(paymentMethodId) {
            const attr = {
                paymentMethodId,
                payment: this.payment,
                name: this.cardholderName,
                email: this.email,
                method: 0,
            };

            axios
                .post("/payment/pay", attr)
                .then((response) => {
                    if (response.data.success) {
                        this.successMessage = response.data.message;
                        this.getCustomerCards();
                        setTimeout(() => {
                            this.successMessage = "";
                        }, 10000);
                    } else {
                        this.errorMessage = response.data.message;
                    }
                })
                .catch((error) => {
                    this.errorMessage = error.message;

                    setTimeout(() => {
                        this.errorMessage = "";
                    }, 10000);
                });
        },
        getCustomerCards() {
            axios.get("/cards").then((res) => {
                this.cards = res.data;
            });
        },
        payWithCard() {
            if (this.selectedCard == null) {
                this.errorMessage = "Please Select Card";
            } else {
                this.errorMessage = "";
                const attr = {
                    card: this.selectedCard,
                    method: 1,
                    payment: 100,
                };
                axios
                    .post("/payment/pay", attr)
                    .then((res) => {
                        this.successMessage = res.data.message;
                        this.getCustomerCards();
                    })
                    .catch((error) => {
                        this.errorMessage = error;
                    });
            }
        },
        submitFormAuthroize() {
            this.successMessage = null;
            this.errorMessage = null;
            const cardDetails = {
                cardNumber: this.cardNumber,
                cvv: this.cvv,
                expiryMonth: this.expiryMonth,
                expiryYear: this.expiryYear,
                name: this.cardholderName,
                email: this.email,
                method: 0,
                payment: 100,
            };
            axios
                .post("/payment/pay", cardDetails)
                .then((response) => {
                    this.successMessage = "Payment successful: " + response.data.message;
                    this.getCustomerCards();
                })
                .catch((error) => {
                    this.errorMessage =
                        "Error making payment: " +
                        (error.response.data.error
                            ? error.response.data.error[0].text
                            : "An unexpected error occurred.");
                });
        },

    },
};
</script>
